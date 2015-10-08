<?php
/**
 * Copyright (C) 2015 Esendex Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the Esendex Community License v1.0 as published by
 * the Esendex Ltd.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * Esendex Community Licence v1.0 for more details.
 *
 * You should have received a copy of the Esendex Community Licence v1.0
 * along with this program.  If not, see <http://www.esendex.com/esendexcommunitylicence/>
 */

use Esendex\DispatchService;
use Esendex\SentMessagesService;
use Esendex\Exceptions\EsendexException;
use Esendex\Model\DispatchMessage;
use Esendex\Model\Message;
use Psr\Log\LoggerInterface;

/**
 * Class Esendex_Sms_Model_Api_Api
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Api_Api
{

    /**
     * @var DispatchService
     */
    protected $dispatchService;

    /**
     * @var SentMessagesService
     */
    protected $sentMessagesService;

    /**
     * @var bool
     */
    protected $performSend = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param DispatchService          $dispatchService
     * @param SentMessagesService      $sentMessagesService
     * @param array                    $config
     */
    public function __construct(
        LoggerInterface $logger,
        DispatchService $dispatchService,
        SentMessagesService $sentMessagesService,
        array $config
    ) {
        $this->dispatchService      = $dispatchService;
        $this->sentMessagesService  = $sentMessagesService;
        $this->parseConfig($config);
        $this->logger = $logger;
    }

    /**
     * @param array $config
     */
    public function parseConfig(array $config)
    {
        if (isset($config['performSend']) && $config['performSend']) {
            $this->performSend = true;
        }
    }

    /**
     * @param int $startIndex
     * @param int $count
     * @return \Esendex\Model\SentMessagesPage
     */
    public function getSentMessages($startIndex = null, $count = null)
    {
        return $this->sentMessagesService->latest($startIndex, $count);
    }

    /**
     * @param Esendex_Sms_Model_Message[] $messages
     */
    public function sendMultipleMessages(array $messages)
    {
        $totalSent = 0;
        foreach ($messages as $message) {
            $totalSent += $this->sendMessage($message);
        }
    }

    /**
     * @param Esendex_Sms_Model_Message $message
     */
    public function sendMessage(Esendex_Sms_Model_Message $message)
    {
        $totalSent = 0;
        foreach ($message->getRecipients() as $recipient) {
            $dispatchMessage = new DispatchMessage(
                $message->getSender(),
                $recipient,
                $message->getMessageBody(),
                Message::SmsType
            );

            if ($this->doSend($dispatchMessage)) {
                $totalSent++;
            }
        }
    }

    /**
     * Returns true if message was actually sent
     *
     * @param DispatchMessage $message
     * @return bool
     */
    public function doSend(DispatchMessage $message)
    {
        $this->logger->debug(sprintf(
            'Sending Message: "%s", To: "%s", From: "%s"',
            $message->body(),
            $message->recipient(),
            $message->originator()
        ));

        if (!$this->performSend) {
            $this->logger->debug('Not performing send as specified in configuration');
            return false;
        }

        try {
            $this->dispatchService->send($message);
            $this->logger->debug('Message was successfully sent');

        } catch (EsendexException $e) {
            //message failed to send
            //or error parsing the result
            $this->logger->critical($e->__toString());
            return false;
        }
        return true;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}

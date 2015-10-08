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

use Esendex\Exceptions\EsendexException;
use Esendex\Model\DispatchMessage;
use Esendex\Model\Message;

/**
 * Class ApiTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ApiTest extends PHPUnit_Framework_TestCase
{
    protected $api;
    protected $logger;
    protected $dispatchService;
    protected $sentMessagesService;

    public function setUp()
    {
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->dispatchService =
            $this->getMockBuilder('Esendex\DispatchService')
                ->disableOriginalConstructor()
                ->getMock();
        $this->sentMessagesService =
            $this->getMockBuilder('Esendex\SentMessagesService')
                ->disableOriginalConstructor()
                ->getMock();
        $this->api = new Esendex_Sms_Model_Api_Api(
            $this->logger,
            $this->dispatchService,
            $this->sentMessagesService
        );
    }

    public function testGetSentMessages()
    {
        $this->sentMessagesService
            ->expects($this->once())
            ->method('latest')
            ->with(1, 15)
            ->will($this->returnValue(array()));

        $this->api->getSentMessages(1, 15);
    }

    public function testGetLogger()
    {
        $this->assertSame($this->logger, $this->api->getLogger());
        $this->api->setLogger($this->logger);
        $this->assertSame($this->logger, $this->api->getLogger());
    }

    public function testDoSendDoesNotSendWithPerformSendDisabled()
    {
        $message = new DispatchMessage('Aydin', 'Mike', 'Magento is so easy to unit test! LOL!', Message::SmsType);

        $this->logger
            ->expects($this->at(0))
            ->method('debug')
            ->with(
                sprintf(
                    'Sending Message: "Magento is so easy to unit test! LOL!", To: "Mike", From: "Aydin"',
                    Message::SmsType
                )
            );

        $this->logger
            ->expects($this->at(1))
            ->method('debug')
            ->with('Not performing send as specified in configuration');

        $this->api->doSend($message);
    }

    public function testDoSendWithPerformSendEnabled()
    {
        $this->api = new Esendex_Sms_Model_Api_Api(
            $this->logger,
            $this->dispatchService,
            $this->sentMessagesService,
            array('performSend' => true)
        );

        $message = new DispatchMessage('Aydin', 'Mike', 'Magento is so easy to unit test! LOL!', Message::SmsType);

        $this->logger
            ->expects($this->at(0))
            ->method('debug')
            ->with(
                sprintf(
                    'Sending Message: "Magento is so easy to unit test! LOL!", To: "Mike", From: "Aydin"',
                    Message::SmsType
                )
            );

        $this->dispatchService
            ->expects($this->once())
            ->method('send')
            ->with($message);

        $this->logger
            ->expects($this->at(1))
            ->method('debug')
            ->with('Message was successfully sent');

        $this->api->doSend($message);
    }

    public function testMessageIsLoggedIfApiThrowsException()
    {
        $this->api = new Esendex_Sms_Model_Api_Api(
            $this->logger,
            $this->dispatchService,
            $this->sentMessagesService,
            array('performSend' => true)
        );

        $message = new DispatchMessage('Aydin', 'Mike', 'Magento is so easy to unit test! LOL!', Message::SmsType);

        $this->logger
            ->expects($this->at(0))
            ->method('debug')
            ->with(
                sprintf(
                    'Sending Message: "Magento is so easy to unit test! LOL!", To: "Mike", From: "Aydin"',
                    Message::SmsType
                )
            );

        $e = new EsendexException(":(");
        $this->dispatchService
            ->expects($this->once())
            ->method('send')
            ->with($message)
            ->will($this->throwException($e));

        $this->logger
            ->expects($this->at(1))
            ->method('critical')
            ->with($e->__toString());

        $this->api->doSend($message);
    }

    public function testSendMessageCallsDoSendForeachRecipient()
    {
        $this->api = new Esendex_Sms_Model_Api_Api(
            $this->logger,
            $this->dispatchService,
            $this->sentMessagesService,
            array('performSend' => true)
        );

        $message = new Esendex_Sms_Model_Message('HELLO WORLD', 'Aydin', array('Nick', 'Joe'));

        $this->dispatchService
            ->expects($this->exactly(2))
            ->method('send')
            ->with($this->isInstanceOf('Esendex\Model\DispatchMessage'));
        $this->api->sendMessage($message);
    }

    public function testSendMessageCallsDoSendWithMultipleMessages()
    {
        $this->api = new Esendex_Sms_Model_Api_Api(
            $this->logger,
            $this->dispatchService,
            $this->sentMessagesService,
            array('performSend' => true)
        );

        $messages = array(
            new Esendex_Sms_Model_Message('HELLO WORLD', 'Aydin', array('Nick', 'Joe')),
            new Esendex_Sms_Model_Message('Magento is so easy to unit test! LOL!', 'Aydin', 'Mike'),
        );

        $this->dispatchService
            ->expects($this->exactly(3))
            ->method('send')
            ->with($this->isInstanceOf('Esendex\Model\DispatchMessage'));

        $this->api->sendMultipleMessages($messages);
    }
}

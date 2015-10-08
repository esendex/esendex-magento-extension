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

/**
 * Class Esendex_Sms_Model_Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Message
{

    /**
     * @var null|string
     */
    protected $messageBody = null;

    /**
     * @var null|string
     */
    protected $sender = null;

    /**
     * @var array
     */
    protected $recipients = [];

    /**
     * @param string        $messageBody
     * @param string        $sender
     * @param array|string  $recipients
     */
    public function __construct($messageBody, $sender, $recipients)
    {

        $this->messageBody  = $messageBody;
        $this->sender       = $sender;

        if (!is_string($recipients) && !is_array($recipients)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Senders must be a string or array of strings. Given: "%s"',
                    is_object($recipients) ? get_class($recipients) : gettype($recipients)
                )
            );
        }

        if (is_string($recipients)) {
            $this->recipients = [$recipients];
        } else {
            $this->recipients = $recipients;
        }
    }

    /**
     * @return null|string
     */
    public function getMessageBody()
    {
        return $this->messageBody;
    }

    /**
     * @param string $messageBody
     */
    public function setMessageBody($messageBody)
    {
        $this->messageBody = $messageBody;
    }

    /**
     * @return null|string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param string $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param array $recipients
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }
}

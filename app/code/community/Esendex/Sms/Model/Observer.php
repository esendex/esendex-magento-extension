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
 * Class Esendex_Sms_Model_Observer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Observer
{

    /**
     * @var Esendex_Sms_Model_MessageProcessor
     */
    protected $messageProcessor;

    /**
     * @var Esendex_Sms_Model_Api_Api
     */
    protected $esendexApi;

    /**
     * @param Esendex_Sms_Model_Resource_Event_Collection $events
     * @param Varien_Object                               $variableContainer
     */
    protected function dispatch($events, Varien_Object $variableContainer = null)
    {
        $messages = $this->getMessageProcessor()->processEvents($events, $variableContainer);

        if (count($messages) > 0) {
            $messagesSentCount = $this->getEsendexApi()->sendMultipleMessages($messages);

            Mage::dispatchEvent(
                'esendex_sms_messages_sent',
                array('messages' => $messages, 'message_count' => $messagesSentCount)
            );
        }
    }

    /**
     * @param Varien_Event_Observer $e
     */
    public function dispatchEvent(Varien_Event_Observer $e)
    {
        $triggerCode = $e->getEvent()->getName();
        $events      = $this->getEvents(Esendex_Sms_Model_Event::TRIGGER_TYPE_EVENT, $triggerCode);

        $this->dispatch($events, $e);
    }

    /**
     * Dispatch For Cron Jobs
     */
    public function dispatchCron(Mage_Cron_Model_Schedule $schedule)
    {
        $triggerCode = $schedule->getJobCode();
        $events      = $this->getEvents(Esendex_Sms_Model_Event::TRIGGER_TYPE_CRON, $triggerCode);

        $this->dispatch($events);
    }

    /**
     * @param string $triggerType
     * @param string $triggerCode
     *
     * @return Esendex_Sms_Model_Resource_Event_Collection
     */
    protected function getEvents($triggerType, $triggerCode)
    {
        $eventCollection = Mage::getModel('esendex_sms/event')
            ->getCollection()
            ->addTriggerFilter($triggerType, $triggerCode);

        return $eventCollection;
    }

    /**
     * @return Esendex_Sms_Model_MessageProcessor_MessageProcessor
     */
    public function getMessageProcessor()
    {
        if (null === $this->messageProcessor) {
            $this->messageProcessor = Esendex_Sms_Model_MessageProcessor_Factory::getInstance();
        }

        return $this->messageProcessor;
    }

    /**
     * @return Esendex_Sms_Model_Api_Api
     */
    public function getEsendexApi()
    {
        if (null === $this->esendexApi) {
            $this->esendexApi = Esendex_Sms_Model_Api_Factory::getInstance();
        }

        return $this->esendexApi;
    }
}

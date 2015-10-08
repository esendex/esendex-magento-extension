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

use Psr\Log\LoggerInterface;

/**
 * Class Esendex_Sms_Model_MessageProcessor_MessageProcessor
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_MessageProcessor_MessageProcessor
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Esendex_Sms_Model_MessageInterpolator
     */
    protected $messageInterpolator;

    /**
     * @param LoggerInterface                       $logger
     * @param Esendex_Sms_Model_MessageInterpolator $messageInterpolator
     */
    public function __construct(
        LoggerInterface $logger,
        Esendex_Sms_Model_MessageInterpolator $messageInterpolator
    ) {
        $this->logger = $logger;
        $this->messageInterpolator = $messageInterpolator;
    }

    /**
     * @param Esendex_Sms_Model_Resource_Event_Collection $events
     * @param Varien_Object|null                          $variableContainer
     *
     * @return array
     */
    public function processEvents(
        Esendex_Sms_Model_Resource_Event_Collection $events,
        Varien_Object $variableContainer = null
    ) {
        $messages = [];
        foreach ($events as $event) {
            /** @var Esendex_Sms_Model_Event $event */

            try {
                /** @var Esendex_Sms_Model_EventProcessor_Interface $eventProcessor */
                $eventProcessor = $event->getEventProcessor();
            } catch (\RuntimeException $e) {
                $this->logger->critical($e->getMessage());
                continue;
            }

            if ($eventProcessor instanceof Esendex_Sms_Model_Logger_LoggerAwareInterface) {
                $eventProcessor->setLogger($this->logger);
            }

            if (null !== $variableContainer) {
                $eventProcessor->setParameters($variableContainer);
            }

            $storeId    = $eventProcessor->getStoreId();
            $triggers   = $this->getTriggers($event, $storeId);
            $messages   = array_merge($messages, $this->processTriggers($triggers, $eventProcessor));
        }

        return $messages;
    }


    /**
     * @param Esendex_Sms_Model_Event $event
     * @param int|null                $storeId
     * @return Esendex_Sms_Model_Resource_Trigger_Collection
     */
    protected function getTriggers($event, $storeId = null)
    {
        $triggerModel = $event->getSaveModel();

        if (null === $triggerModel) {
            $triggerModel = 'esendex_sms/trigger';
        }

        /** @var Esendex_Sms_Model_Resource_Trigger_Collection $triggers */
        $triggers = Mage::getModel($triggerModel)
            ->getCollection()
            ->addEvents()
            ->addEventIdFilter($event->getId())
            ->addStatusEnabledFilter();

        if (null !== $storeId) {
            $triggers->addStoreFilter((int) $storeId);
        }

        return $triggers;
    }
    /**
     * Get the message for each trigger
     * Do variable replacing and post-processing
     *
     * @param Esendex_Sms_Model_Resource_Trigger_Collection $triggers
     * @param Esendex_Sms_Model_EventProcessor_Interface    $eventProcessor
     *
     * @return array
     */
    protected function processTriggers(
        Esendex_Sms_Model_Resource_Trigger_Collection $triggers,
        Esendex_Sms_Model_EventProcessor_Interface $eventProcessor
    ) {

        $messages = array();
        foreach ($triggers as $trigger) {

            if (!$eventProcessor->shouldSend($trigger)) {
                continue;
            }

            $recipients = $eventProcessor->getRecipient($trigger);
            $sender     = $trigger->getData('sender');

            //replace the placeholders with actual variables
            $messageBody = $this->messageInterpolator->interpolate(
                $trigger->getMessageTemplate(),
                $eventProcessor->getVariableContainer($trigger),
                $eventProcessor->getVariables()
            );

            $messageBody    = $eventProcessor->postProcess($messageBody);
            $message        = new Esendex_Sms_Model_Message($messageBody, $sender, $recipients);
            $messages[]     = $message;
        }

        return $messages;
    }
}

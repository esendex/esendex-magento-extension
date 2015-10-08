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
 * Class Esendex_Sms_Model_Resource_Setup
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * Remove an event using the entity_id
     *
     * @param int $id
     * @return int
     */
    public function removeEvent($id)
    {
        $result = $this->getConnection()->delete(
            $this->getTable('esendex_sms/event'),
            sprintf('entity_id = %s', $id)
        );

        //Flush Event Collection from Cache
        Mage::app()->getCacheInstance()->clean(array(Esendex_Sms_Model_Event::CACHE_TAG));

        return $result;
    }

    /**
     * Create a new SMS event
     *
     * @param string $name
     * @param string $triggerType
     * @param string $triggerCode
     * @param int $order
     * @param string|null $eventProcessorModel
     * @param string|null $saveModel
     *
     * @internal param string $model
     */
    public function addEvent($name, $triggerType, $triggerCode, $order, $eventProcessorModel = null, $saveModel = null)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid Name. Expected: "string", got: "%s"',
                    is_object($name) ? get_class($name) : gettype($name)
                )
            );
        }

        if (!is_string($triggerCode)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid Trigger Code. Expected: "string", got: "%s"',
                    is_object($triggerCode) ? get_class($triggerCode) : gettype($triggerCode)
                )
            );
        }

        if (!is_int($order)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid Order. Expected: "int", got: "%s"',
                    is_object($triggerCode) ? get_class($triggerCode) : gettype($triggerCode)
                )
            );
        }

        if ($saveModel !== null) {
            //Check model exists
            if (!Mage::getModel($saveModel)) {
                throw new InvalidArgumentException(sprintf('Save Model: "%s" does not exist', $saveModel));
            }
        }

        if ($eventProcessorModel !== null) {
            //Check model exists
            if (!Mage::getModel($eventProcessorModel)) {
                throw new InvalidArgumentException(sprintf('Event Processor Model: "%s" does not exist', $eventProcessorModel));
            }
        }

        //Check trigger type
        if ($triggerType !== Esendex_Sms_Model_Event::TRIGGER_TYPE_EVENT
            && $triggerType !== Esendex_Sms_Model_Event::TRIGGER_TYPE_CRON
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid Trigger Type give: "%s", Accepted types are: "%s"',
                    $triggerType,
                    implode(
                        '", "',
                        array(
                            Esendex_Sms_Model_Event::TRIGGER_TYPE_EVENT,
                            Esendex_Sms_Model_Event::TRIGGER_TYPE_CRON
                        )
                    )
                )
            );
        }

        $this->getConnection()->insert($this->getTable('esendex_sms/event'), array(
            'save_model'        => $saveModel,
            'event_processor'   => $eventProcessorModel,
            'name'              => $name,
            'trigger_type'      => $triggerType,
            'trigger_code'      => $triggerCode,
            'order'             => $order,
        ));

        // Flush Event Collection from Cache
        Mage::app()->getCacheInstance()->clean(array(Esendex_Sms_Model_Event::CACHE_TAG));

        //Return the ID of the newly created Event
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Add a sample message to a particular event for a particular Locale.
     *
     * Eg: addSampleMessage(1, 'en_US', 'Hey $NAME$ - Your Order has shipped!'
     *
     * @param $eventIdOrName $eventId
     * @param string $localeCode
     * @param string $message
     */
    public function addSampleMessage($eventIdOrName, $localeCode, $message)
    {

        if (!is_int($eventIdOrName) && !is_string($eventIdOrName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid Event ID. Expected: string or int, got: "%s"',
                    is_object($eventIdOrName) ? get_class($eventIdOrName) : gettype($eventIdOrName)
                )
            );
        }

        if (!is_string($localeCode)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid Locale. Expected: "%s", got: "%s"',
                    is_object($localeCode) ? get_class($localeCode) : gettype($localeCode)
                )
            );
        }

        $locales = Zend_Locale::getLocaleList();
        if (!isset($locales[$localeCode])) {
            throw new InvalidArgumentException(
                sprintf('Invalid Locale. "%s" is not a valid Locale', $localeCode)
            );
        }

        if (!is_string($message)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid Message Template. Expected: "%s", got: "%s"',
                    is_object($message) ? get_class($message) : gettype($message)
                )
            );
        }

        $eventId = $eventIdOrName;
        if (!is_int($eventIdOrName)) {
            $event = $this->loadEvent($eventIdOrName);
            if (!is_array($event)) {
                throw new \InvalidArgumentException(sprintf('Cannot find event with name: "%s"', $eventIdOrName));
            }
            $eventId = $event['entity_id'];
        }

        $this->getConnection()->insert($this->getTable('esendex_sms/event_sample_message_template'), array(
            'event_id'          => $eventId,
            'locale_code'       => $localeCode,
            'message_template'  => $message,
        ));

        //Flush Event Collection from Cache
        Mage::app()->getCacheInstance()->clean(array(Esendex_Sms_Model_Event::CACHE_TAG));
    }

    /**
     * @param string $eventName
     *
     * @return array
     */
    public function loadEvent($eventName)
    {
        $select = $this->getConnection()->select()
            ->from(array('e' => $this->getTable('esendex_sms/event')))
            ->where('e.name = ?', $eventName);

        return $this->getConnection()->fetchRow($select);
    }
}
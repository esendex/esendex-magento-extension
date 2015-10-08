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
class Esendex_Sms_Model_Event extends Mage_Core_Model_Abstract
{
    const ENTITY    = 'esendex_sms_event';
    const CACHE_TAG = 'esendex_sms_event';

    /**
     * Trigger types, Magento Cron or Magento Events
     */
    const TRIGGER_TYPE_CRON     = 'cron';
    const TRIGGER_TYPE_EVENT    = 'event';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'esendex_sms_event';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'event';

    /**
     * Constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('esendex_sms/event');
    }

    /**
     * @return self
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * @return self
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * Get the Event Processor Model for this event
     *
     * @return Esendex_Sms_Model_EventProcessor_Interface $model
     */
    public function getEventProcessor()
    {
        $modelAlias     = $this->getData('event_processor');
        $eventProcessor = Mage::getModel($modelAlias);

        if (false === $eventProcessor) {
            throw new \RuntimeException(
                sprintf('Event Processor Model: "%s" does not have an associated class', $modelAlias)
            );
        }

        if (!$eventProcessor instanceof Esendex_Sms_Model_EventProcessor_Interface) {
            throw new \RuntimeException(
                sprintf(
                    'Event Processor Model: "%s" must implement: "Esendex_Sms_Model_EventProcessor_Interface"',
                    $modelAlias
                )
            );
        }

        return $eventProcessor;
    }
}

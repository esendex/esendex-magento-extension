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
 * Class Esendex_Sms_Model_Trigger
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
abstract class Esendex_Sms_Model_TriggerAbstract
    extends Mage_Core_Model_Abstract
    implements Esendex_Sms_Model_ValidatableInterface
{
    const ENTITY    = 'esendex_sms_trigger';
    const CACHE_TAG = 'esendex_sms_trigger';

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'esendex_sms_trigger';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'trigger';

    /**
     * @var Esendex_Sms_Model_Event|null
     */
    protected $event;

    /**
     * Constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('esendex_sms/trigger');
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
     * Load the Event for this trigger
     */
    protected function _afterLoad()
    {
        $event = Mage::getModel('esendex_sms/event')->load($this->getEventId());
        $this->setData('event', $event);
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [
            'status' => 1,
        ];
        return $values;
    }

    /**
     * @return Esendex_Sms_Model_Event|null
     */
    public function getEvent()
    {
        if (!$this->event && $this->getData('event_id')) {
            $this->event = Mage::getModel('esendex_sms/event')->load($this->getData('event_id'));
        }

        return $this->event;
    }

    /**
     * Filter & validate data. SOC anyone?
     */
    public function validate()
    {
        if ($this->getEvent()) {
            $eventModel = $this->getEvent()->getEventProcessor();

            $vars = array_map(function (Esendex_Sms_Model_Variable $var) {
                return $var->getReplaceName();
            }, $eventModel->getVariables());

            $res = preg_match_all(
                '/\$[a-zA-Z]+[a-zA-Z\d_]*\$/m',
                $this->getData('message_template'),
                $matches
            );

            if ($res) {
                foreach ($matches[0] as $placeHolder) {
                    if (!in_array($placeHolder, $vars)) {
                        $this->addError(sprintf('"%s" is not an available variable', $placeHolder));
                    }
                }
            }
        }

        return !$this->hasErrors();
    }

    /**
     * @param string $error
     */
    public function addError($error)
    {
        $this->errors[] = Mage::helper('esendex_sms')->__($error);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }
}

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
 * Class Esendex_Sms_Model_Resource_Trigger
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
final class Esendex_Sms_Model_Resource_Trigger extends Esendex_Sms_Model_Resource_TriggerAbstract
{
    /**
     * @param null $eventId
     *
     * @return false|Mage_Core_Model_Abstract
     */
    public function getTriggerModel($eventId = null)
    {
        $saveModel = static::DEFAULT_MODEL;

        if (null === $eventId) {
            return Mage::getModel($saveModel);
        }

        //$event = Mage::getModel('esendex_sms/event')->load($eventId);

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('esendex_sms/event'), ['save_model'])
            ->where('entity_id =?', $eventId);

        $customSaveModel = $this->_getReadAdapter()->fetchOne($select);

        //if there is no save_model specified we use the default model
        if ($customSaveModel) {
            $saveModel = $customSaveModel;
        }

        if (!$trigger = Mage::getModel($saveModel)) {
            throw new \RuntimeException(sprintf('Save Model: "%s" not found', $saveModel));
        }

        return $trigger;
    }

    /**
     * @param int $triggerId
     *
     * @return string
     */
    public function getEventIdByTriggerId($triggerId)
    {
        $read           = $this->_getReadAdapter();
        $field          = $this->getIdFieldName();
        $fieldColumn    = $read
            ->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));

        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), ['event_id'])
            ->where($fieldColumn . '=?', $triggerId);

        //get the event id using this trigger ID
        $eventId = $read->fetchOne($select);
        return $eventId;
    }

    /**
     * Load an object
     *
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (is_null($field) || $field === $this->getIdFieldName()) {

            $eventId = $this->getEventIdByTriggerId($value);
            $trigger = $this->getTriggerModel($eventId);

            if ($trigger instanceof Esendex_Sms_Model_Trigger) {
                parent::load($object, $value, $field);
                return $object;
            }

            return $trigger->load($value, $field);
        }

        return parent::load($object, $value, $field);
    }
}

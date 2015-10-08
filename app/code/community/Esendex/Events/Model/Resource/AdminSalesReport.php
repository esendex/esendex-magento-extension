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
 * Class Esendex_Sms_Model_Resource_Trigger_AdminSalesReport
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Events_Model_Resource_AdminSalesReport extends Esendex_Sms_Model_Resource_TriggerAbstract
{

    /**
     * Constructor
     */
    public function _construct()
    {
        $this->_init('esendex_sms/trigger', 'entity_id');
    }

    /**
     * Get Recipients for this trigger
     *
     * @param int $triggerId
     * @return array
     */
    public function lookupRecipients($triggerId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('esendex_events/admin_sales_report_recipients'), 'recipient')
            ->where('trigger_id = ?', (int) $triggerId);
        return $adapter->fetchCol($select);
    }

    /**
     * Get Details for this trigger
     *
     * @param $triggerId
     * @return array
     */
    public function lookupDetails($triggerId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('esendex_events/admin_sales_report_details'))
            ->where('trigger_id = ?', (int) $triggerId);
        return $adapter->fetchRow($select);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return self
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $recipients = $this->lookupRecipients($object->getId());
            $object->setData('recipients', $recipients);

            if ($details = $this->lookupDetails($object->getId())) {
                unset($details['trigger_id']);
                foreach ($details as $name => $value) {
                    $object->setData($name, $value);
                }
            }
        }
        return parent::_afterLoad($object);
    }

    /**
     * Assign trigger to store views
     *
     * @param Mage_Core_Model_Abstract $object
     * @return self
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        // Get any previous details or empty array
        $detailsData = $this->lookupDetails($object->getId());

        if (!is_array($detailsData)) {
            $detailsData = array();
        }

        // Construct the new detail row
        $newDetails = array(
            'trigger_id' => $object->getId(),
            'frequency'  => $object->getData('frequency'),
            'start_date' => $object->getData('start_date')
        );

        // If there diff then delete and save new details
        if (array_diff_assoc($newDetails, $detailsData)) {
            // Delete Details
            $table = $this->getTable('esendex_events/admin_sales_report_details');
            $this->_getWriteAdapter()->delete($table, array('trigger_id = ?' => (int) $object->getId()));

            // Write New Details
            $defualt = array('trigger_id' => (int) $object->getId());
            $details = array_merge($defualt, $newDetails);
            $this->_getWriteAdapter()->insert($table, $details);
        }

        // Save Recipients
        $oldRecipients = $this->lookupRecipients($object->getId());
        $newRecipients = (array) $object->getData('recipients');

        $table  = $this->getTable('esendex_events/admin_sales_report_recipients');
        $insert = array_diff($newRecipients, $oldRecipients);
        $delete = array_diff($oldRecipients, $newRecipients);
        if (count($delete)) {
            $where = array(
                'trigger_id = ?'   => (int) $object->getId(),
                'recipient IN (?)' => $delete
            );
            $this->_getWriteAdapter()->delete($table, $where);
        }
        if (count($insert)) {
            $data = array();
            foreach ($insert as $recipient) {
                $data[] = array(
                    'trigger_id' => (int) $object->getId(),
                    'recipient'  => $recipient
                );
            }
            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }
        return parent::_afterSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        // Delete Recipients
        $table  = $this->getTable('esendex_events/admin_sales_report_recipients');
        $this->_getWriteAdapter()->delete($table, ['trigger_id = ?' => (int) $object->getId()]);

        // Delete Details
        $table  = $this->getTable('esendex_events/admin_sales_report_details');
        $this->_getWriteAdapter()->delete($table, ['trigger_id = ?' => (int) $object->getId()]);

        return parent::_beforeDelete($object);
    }
}

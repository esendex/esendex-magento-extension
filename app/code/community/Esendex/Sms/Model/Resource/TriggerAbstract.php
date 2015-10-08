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
abstract class Esendex_Sms_Model_Resource_TriggerAbstract extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Default Model to use for triggers
     */
    const DEFAULT_MODEL = 'esendex_sms/trigger';

    /**
     * Constructor
     */
    public function _construct()
    {
        $this->_init('esendex_sms/trigger', 'entity_id');
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $triggerId
     * @return array
     */
    public function lookupStoreIds($triggerId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('esendex_sms/trigger_store'), 'store_id')
            ->where('trigger_id = ?',(int) $triggerId);
        return $adapter->fetchCol($select);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return self
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            //get stores
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Esendex_Sms_Model_Trigger $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $storeIds = [Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId()];
            $select->join(
                ['sms_trigger_store' => $this->getTable('esendex_sms/trigger_store')],
                $this->getMainTable() . '.entity_id = sms_trigger_store.trigger_id',
                []
            )
            ->where('sms_trigger_store.store_id IN (?)', $storeIds)
            ->order('sms_trigger_store.store_id DESC')
            ->limit(1);
        }

        return $select;
    }

    /**
     * Assign trigger to store views
     *
     * @param Mage_Core_Model_Abstract $object
     * @return self
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table  = $this->getTable('esendex_sms/trigger_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if (count($delete)) {
            $where = [
                'trigger_id = ?' => (int) $object->getId(),
                'store_id IN (?)' => $delete
            ];
            $this->_getWriteAdapter()->delete($table, $where);
        }
        if (count($insert)) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    'trigger_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                ];
            }
            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }
        return parent::_afterSave($object);
    }
}

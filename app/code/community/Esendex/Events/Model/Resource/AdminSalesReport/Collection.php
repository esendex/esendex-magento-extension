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
 * Class Esendex_Events_Model_Resource_AdminSalesReport_Collection
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Events_Model_Resource_AdminSalesReport_Collection
    extends Esendex_Sms_Model_Resource_Trigger_Collection
{

    /**
     * Set Model Class
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('esendex_events/adminSalesReport');
    }

    /**
     * Join data from other tables
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        //add details - eg frequency
        $this->mergeRelationalData($this->getTable('esendex_events/admin_sales_report_details'));

        //add stores
        $this->addRelationalData($this->getTable('esendex_sms/trigger_store'), 'store_id');

        // recipients
        $this->addRelationalData(
            $this->getTable('esendex_events/admin_sales_report_recipients'),
            'recipient',
            'recipients'
        );
    }

    /**
     * Join data from a related table. Eg put all store_id's from another table
     * on to $trigger['store_ids'] using trigger id to join.
     *
     * @param string      $table
     * @param string      $column
     * @param null|string $resultKey
     */
    public function addRelationalData($table, $column, $resultKey = null)
    {
        if (null === $resultKey) {
            $resultKey = $column;
        }

        $triggerIds = array();
        foreach ($this->_items as $item) {
            $triggerIds[] = $item->getId();
        }

        $select = $this->getConnection()->select();
        $select->from($table, array($column, 'trigger_id'))
            ->where('trigger_id IN (?)', $triggerIds);

        $rows = $this->getConnection()->fetchAll($select);

        $rowsByTriggerId = array();
        foreach ($rows as $row) {
            $triggerId = $row['trigger_id'];

            if (!isset($rowsByTriggerId[$triggerId])) {
                $rowsByTriggerId[$triggerId] = array($row[$column]);
            } else {
                $rowsByTriggerId[$triggerId][] = $row[$column];
            }
        }

        foreach ($rowsByTriggerId as $triggerId => $values) {
            $item = $this->getItemByColumnValue($item->getIdFieldName(), $triggerId);
            $item->setData($resultKey, $values);
        }
    }

    /**
     * Merge data from another table. Eg one -> one data.
     *
     * @param string $table
     */
    public function mergeRelationalData($table)
    {
        $triggerIds = array();
        foreach ($this->_items as $item) {
            $triggerIds[] = $item->getId();
        }

        $select = $this->getConnection()->select();
        $select->from($table)
            ->where('trigger_id IN (?)', $triggerIds);

        $rows = $this->getConnection()->fetchAll($select);

        foreach ($rows as $row) {
            $triggerId  = $row['trigger_id'];
            $item       = $this->getItemByColumnValue($item->getIdFieldName(), $triggerId);
            $item->addData($row);
        }
    }
}
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
 * Class Esendex_Sms_Model_Resource_Trigger_Collection
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Resource_Trigger_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * @var array
     */
    protected $_joinedFields = [];

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('esendex_sms/trigger');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Add filter by store
     * @param int|Mage_Core_Model_Store $store
     * @param bool $withAdmin
     * @return self
     *
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!isset($this->_joinedFields['store'])) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = [$store->getId()];
            }
            if (!is_array($store)) {
                $store = [$store];
            }
            if ($withAdmin) {
                $store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
            }
            $this->addFilter('store', ['in' => $store], 'public');
            $this->_joinedFields['store'] = true;
        }
        return $this;
    }

    /**
     * Join Events table
     *
     * @return self
     */
    public function addEvents()
    {
        $this->join(
            [ 'e' => 'event'],
            'main_table.event_id = e.entity_id',
            [
                'name' => 'e.name'
            ]
        );

        return $this;
    }

    /**
     * @return self
     */
    public function addStatusEnabledFilter()
    {
        $this->addFilter('status', array('eq' => 1));
        return $this;
    }

    /**
     * @param int $eventId
     * @return self
     */
    public function addEventIdFilter($eventId)
    {
        $this->addFilter('event_id', array('eq' => (int) $eventId));
        return $this;
    }

    /**
     * Restrict collection to only Mobile Sales Report triggers
     *
     * @return $this
     */
    public function onlyMobileSalesReports()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('esendex_sms/event'), array('entity_id'))
            ->where('name = ?', 'Admin Sales Report');

        $mobileSalesReportEventId = $this->getConnection()->fetchOne($select);

        $this->addFieldToFilter('event_id', array('eq' => (int) $mobileSalesReportEventId));
        return $this;
    }

    /**
     * Restrict collection show it does not show Mobile Sales Report triggers
     *
     * @return $this
     */
    public function notMobileSalesReports()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('esendex_sms/event'), array('entity_id'))
            ->where('name = ?', 'Admin Sales Report');

        $mobileSalesReportEventId = $this->getConnection()->fetchOne($select);

        $this->addFieldToFilter('event_id', array('neq' => (int) $mobileSalesReportEventId));
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return self
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('esendex_sms/trigger_store')],
                'main_table.entity_id = store_table.trigger_id',
                []
            )->group('main_table.entity_id');
            /*
             * Allow analytic functions usage because of one field grouping
             */
            $this->_useAnalyticFunction = true;
        }
        return parent::_renderFiltersBefore();
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }
}

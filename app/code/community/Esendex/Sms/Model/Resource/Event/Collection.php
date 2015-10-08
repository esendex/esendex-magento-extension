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
 * Class Esendex_Sms_Model_Resource_Event_Collection
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Resource_Event_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * @var array
     */
    protected $_joinedFields = [];

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('esendex_sms/event');

        // Enable caching for collection
        $cache = Mage::app()->getCacheInstance();
        $this->initCache($cache, 'esendex_sms_collection', array(Esendex_Sms_Model_Event::CACHE_TAG));
    }

    /**
     * Init collection select
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _initSelect()
    {
        $localeCode         = Mage::app()->getLocale()->getLocaleCode();
        $quotedLocaleCode   = $this->getSelect()->getAdapter()->quote($localeCode);

        $this->getSelect()
            ->from(array('main_table' => $this->getMainTable()))
            ->joinLeft(
                [ 's' => $this->getTable('event_sample_message_template')],
                'main_table.entity_id = s.event_id AND s.locale_code = ' . $quotedLocaleCode ,
                ['sample_message_template' => 's.message_template',]
            );

        return $this;
    }

    /**
     * Get triggers as array
     *
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = 'entity_id', $labelField = 'name', $additional = [])
    {
        $data = parent::_toOptionArray($valueField, $labelField, array('order' => 'order'));

        usort($data, function ($a, $b) {
           return $a['order'] - $b['order'];
        });

        return array_map(
            function (array $row) {
                unset($row['order']);
                return $row;
            },
            $data
        );
    }

    /**
     * Get options hash
     *
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    protected function _toOptionHash($valueField = 'entity_id', $labelField = 'name')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * @param $triggerType
     * @param $triggerCode
     * @return self
     */
    public function addTriggerFilter($triggerType, $triggerCode)
    {
        $this->addFieldToFilter('trigger_type', $triggerType);
        $this->addFieldToFilter('trigger_code', $triggerCode);
        return $this;
    }

    /**
     * Restrict collection to only Mobile Sales Report triggers
     *
     * @return $this
     */
    public function onlyMobileSalesReports()
    {
        $this->addFieldToFilter('name', array('eq' => 'Admin Sales Report'));
        return $this;
    }

    /**
     * Restrict collection show it does not show Mobile Sales Report triggers
     *
     * @return $this
     */
    public function notMobileSalesReports()
    {
        $this->addFieldToFilter('name', array('neq' => 'Admin Sales Report'));
        return $this;
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

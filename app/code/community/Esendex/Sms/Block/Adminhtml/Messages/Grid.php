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
 * Class Esendex_Sms_Block_Adminhtml_Sender_Grid
 *
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Sms_Block_Adminhtml_Messages_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sentMessagesGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        // Use our custom grid template as Esendex API max page size is 100.
        // If we don't remove the grid page size of 200 the paging will go out of sync.
        $this->setTemplate('esendex/widget/messages/grid.phtml');
    }

    /**
     * Prepare collection
     *
     * @return self
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('esendex_sms/sentMessages');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Get collection to allow for better unit tests
     *
     * @return Esendex_Sms_Model_SentMessages
     */
    public function getCollection()
    {
        if (!$this->_collection) {
            $this->_collection = Mage::getModel('esendex_sms/sentMessages');
        }

        return $this->_collection;
    }

    /**
     * Prepare grid collection
     *
     * @return self
     */
    protected function _prepareColumns()
    {
        $this->addColumn('type', array(
            'header'    => Mage::helper('esendex_sms')->__('Type'),
            'align'     => 'left',
            'index'     => 'type',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('from', array(
            'header'    => Mage::helper('esendex_sms')->__('From'),
            'align'     => 'left',
            'index'     => 'originator',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('recipient', [
            'header'    => Mage::helper('esendex_sms')->__('Recipient'),
            'align'     => 'left',
            'index'     => 'recipient',
            'filter'    => false,
            'sortable'  => false,
        ]);

        $this->addColumn('summary', array(
            'header'    => Mage::helper('esendex_sms')->__('Summary'),
            'align'     => 'left',
            'index'     => 'summary',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('esendex_sms')->__('Status'),
            'align'     => 'left',
            'index'     => 'status',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('submittedAt', array(
            'header'    => Mage::helper('esendex_sms')->__('Submitted At'),
            'align'     => 'left',
            'index'     => 'submittedAt',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('sentAt', array(
            'header'    => Mage::helper('esendex_sms')->__('Sent At'),
            'align'     => 'left',
            'index'     => 'sentAt',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('deliveredAt', array(
            'header'    => Mage::helper('esendex_sms')->__('Delivered At'),
            'align'     => 'left',
            'index'     => 'deliveredAt',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('lastStatusAt', array(
            'header'    => Mage::helper('esendex_sms')->__('Last Status At'),
            'align'     => 'left',
            'index'     => 'lastStatusAt',
            'filter'    => false,
            'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Get the row url, prevent rows being clickable
     *
     * @param Esendex_Sms_Model_Trigger
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * Get the grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Override _preparePage to track prev page and size
     */
    protected function _preparePage()
    {
        $session        = Mage::getSingleton('adminhtml/session');
        $paramPrefix    = $this->getId();
        $prevPage       = null;
        $prevSize       = null;

        // Get previous page and size before it is overwrote
        if ($this->_saveParametersInSession && ($param = $session->getData($paramPrefix . $this->getVarNamePage()))) {
            $prevPage = $param;
        }

        if ($this->_saveParametersInSession && ($param = $session->getData($paramPrefix . $this->getVarNameLimit()))) {
            $prevSize = $param;
        }

        // Get param will get from request and set in session
        $newPage = (int) $this->getParam($this->getVarNamePage(), $this->_defaultPage);
        $newSize = (int) $this->getParam($this->getVarNameLimit(), $this->_defaultLimit);

        // Figure out what page and size we want
        if ($prevPage && $prevSize) {
            if ($prevSize !== $newSize) {
                // Changing the page size so calculate what page we now want to be on
                // by getting the closest offset to previous position
                $prevOffset = $prevSize * ($prevPage -1);
                $newPage    = ceil($prevOffset / $newSize);
            }
        }

        // Set new values in the session!
        $session->setData($paramPrefix . $this->getVarNamePage(), $newPage);
        $session->setData($paramPrefix . $this->getVarNameLimit(), $newSize);

        // Set the values on the collection
        $this->getCollection()->setCurPage((int) $newPage);
        $this->getCollection()->setPageSize((int) $newSize);
    }
}

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
 * Class Esendex_Sms_Block_Adminhtml_Trigger_Grid
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Block_Adminhtml_Trigger_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('triggerGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection
     *
     * @return self
     */
    protected function _prepareCollection()
    {
        if (!$this->getCollection()) {
            $collection = Mage::getModel('esendex_sms/trigger')
                ->getCollection()
                ->addEvents()
                ->notMobileSalesReports();

            $this->setCollection($collection);
        }

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid collection
     *
     * @return self
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('esendex_sms')->__('Id'),
            'index'     => 'entity_id',
            'type'      => 'number'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('esendex_sms')->__('Event'),
            'align'     => 'left',
            'index'     => 'name',
            'renderer'  => 'Esendex_Sms_Block_Adminhtml_Grid_Renderer_Translate'
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('esendex_sms')->__('Description'),
            'align'     => 'left',
            'index'     => 'description',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('esendex_sms')->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                '1' => Mage::helper('esendex_sms')->__('Enabled'),
                '0' => Mage::helper('esendex_sms')->__('Disabled'),
            )
        ));

        $this->addColumn('store_id', array(
            'header'                    => Mage::helper('esendex_sms')->__('Store Views'),
            'index'                     => 'store_id',
            'type'                      => 'store',
            'store_all'                 => true,
            'store_view'                => true,
            'sortable'                  => false,
            'filter_condition_callback' => array($this, '_filterStoreCondition'),
        ));

        $this->addColumn('sender', array(
            'header'        => Mage::helper('esendex_sms')->__('From'),
            'index'         => 'sender',
            'store_all'     => true,
            'store_view'    => true,
            'sortable'      => true,
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('esendex_sms')->__('Created at'),
            'index'     => 'created_at',
            'width'     => '120px',
            'type'      => 'datetime',
        ));
        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('esendex_sms')->__('Updated at'),
            'index'     => 'updated_at',
            'width'     => '120px',
            'type'      => 'datetime',
        ));

        $this->addColumn('action', array(
            'header'    =>  Mage::helper('esendex_sms')->__('Action'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('esendex_sms')->__('Edit'),
                    'url'       => array('base'=> '*/*/edit'),
                    'field'     => 'id'
                )
            ),
            'filter'    => false,
            'is_system' => true,
            'sortable'  => false,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('esendex_sms')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('esendex_sms')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('esendex_sms')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * Prepare mass action
     *
     * @return self
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('trigger');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'     => Mage::helper('esendex_sms')->__('Delete'),
            'url'       => $this->getUrl('*/*/massDelete'),
            'confirm'   => Mage::helper('esendex_sms')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('esendex_sms')->__('Change status'),
            'url'   => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'status' => array(
                    'name'      => 'status',
                    'type'      => 'select',
                    'class'     => 'required-entry',
                    'label'     => Mage::helper('esendex_sms')->__('Status'),
                    'values'    => array(
                        '1' => Mage::helper('esendex_sms')->__('Enabled'),
                        '0' => Mage::helper('esendex_sms')->__('Disabled'),
                    )
                )
            )
        ));

        return $this;
    }

    /**
     * Get the row url
     *
     * @param Esendex_Sms_Model_Trigger
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
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
     * after collection load
     * @return self
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * Filter store column
     *
     * @param Esendex_Sms_Model_Resource_Trigger_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return self
     */
    protected function _filterStoreCondition(
        Esendex_Sms_Model_Resource_Trigger_Collection $collection,
        Mage_Adminhtml_Block_Widget_Grid_Column $column
    ) {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addStoreFilter($value);
        return $this;
    }
}

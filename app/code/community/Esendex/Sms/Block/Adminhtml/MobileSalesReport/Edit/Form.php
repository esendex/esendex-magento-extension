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
use Esendex\Http\HttpException;

/**
 * Class Esendex_Sms_Block_Adminhtml_MobileSalesReport_Edit_Form
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Sms_Block_Adminhtml_MobileSalesReport_Edit_Form extends Esendex_Sms_Block_Adminhtml_Trigger_Edit_Form
{

    /**
     * The label for the Form Fieldset
     *
     * @var string
     */
    protected $notificationLegend = 'Admin Sales Report';

    /**
     * Get the Trigger Events Collection from Cache
     *
     * @return Esendex_Sms_Model_Resource_Event_Collection
     */
    protected function getEvents()
    {
        return Mage::getSingleton('esendex_sms/event')
            ->getCollection()
            ->onlyMobileSalesReports();
    }

    /**
     * Override to switch store element to single select
     *
     * @param Varien_Data_Form_Element_Fieldset $stage1Fieldset
     */
    protected function prepareStage1(Varien_Data_Form_Element_Fieldset $stage1Fieldset)
    {
        parent::prepareStage1($stage1Fieldset);

        if (Mage::app()->isSingleStoreMode()) {
            return;
        }

        // Remove the multiselect store id element
        $stage1Fieldset->removeField('store_id');

        // Get store values without all views option
        $stores = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false);

        // Add a select element for the stores
        $field = $stage1Fieldset->addField('store_id', 'select', array(
            'name'     => 'stores[]',
            'label'    => Mage::helper('esendex_sms')->__('Store Views'),
            'title'    => Mage::helper('esendex_sms')->__('Store Views'),
            'required' => true,
            'values'   => $stores,
        ));

        // Use store switcher field rendered
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
    }
}

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
 * Class Esendex_Sms_Model_Listener_TriggerCreate_AdminSalesReport
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Events_Model_Listener_TriggerCreate_AdminSalesReport
{
    /**
     * @param Varien_Event_Observer $e
     */
    public function addFields(Varien_Event_Observer $e)
    {
        $fieldset           = $e->getFieldset();
        $recipientsHelpText = 'Enter one mobile number per line. To send internationally ';
        $recipientsHelpText .= 'please add the country code e.g. +447875123456';
        $afterElementHtml   = sprintf(
            '<p class="nm"><small>%s</small></p>',
            Mage::helper('esendex_sms')->__($recipientsHelpText)
        );

        $fieldset->addField('recipients', 'textarea', array(
            'label'              => Mage::helper('esendex_sms')->__('Recipients'),
            'name'               => 'recipients',
            'required'           => true,
            'after_element_html' => $afterElementHtml,
        ));

        // Translate frequencies
        $frequencies = array_map(function($frequency) {
            return Mage::helper('esendex_sms')->__($frequency);
        },Esendex_Events_Model_EventProcessor_AdminSalesReport::$frequencies);

        $fieldset->addField('frequency', 'select', array(
            'label'    => Mage::helper('esendex_sms')->__('Frequency'),
            'name'     => 'frequency',
            'required' => true,
            'options'  => $frequencies,
        ));

        // Note: Magento JS validation does not correctly validate dates
        // We have hardcoded the format of the date to y/MM/d to help
        // Research indicates this is the most supported format

        $fieldset->addField('start_date', 'date', array(
            'label'    => Mage::helper('esendex_sms')->__('Start Date'),
            'name'     => 'start_date',
            'required' => true,
            'format'   => 'y/MM/d',
            'image'    => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
            'class'    => 'validate-date',
            'note'     => Mage::helper('esendex_sms')->__('Should be a date in the future')
        ));
    }
}
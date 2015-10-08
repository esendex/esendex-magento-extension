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
 * Class Esendex_Sms_Model_Listener_TriggerCreate_OrderStatusChange
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Events_Model_Listener_TriggerCreate_OrderStatusChange
{
    /**
     * Add second stage form fields
     *
     * @param Varien_Event_Observer $e
     */
    public function addFields(Varien_Event_Observer $e)
    {
        $fieldset   = $e->getFieldset();
        $statuses   = Mage::getModel('sales/order_status')->getResourceCollection()->toOptionArray();

        $fieldset->addField('statuses', 'multiselect', array(
            'name'     => 'statuses[]',
            'label'    => Mage::helper('esendex_sms')->__('Statuses to be Notified'),
            'title'    => Mage::helper('esendex_sms')->__('Status'),
            'required' => true,
            'values'   => $statuses
        ));
    }
}
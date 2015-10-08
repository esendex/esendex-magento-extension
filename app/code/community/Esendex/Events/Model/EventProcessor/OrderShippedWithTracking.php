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
 * Class Esendex_Events_Model_EventProcessor_OrderShippedWithTracking
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Events_Model_EventProcessor_OrderShippedWithTracking
    extends Esendex_Events_Model_EventProcessor_OrderAbstract
    implements Esendex_Sms_Model_EventProcessor_Interface
{
    /**
     * @var array
     */
    protected $variables = array(
        // Order Variables
        'order::customer_firstname' => 'firstname',
        'order::customer_lastname'  => 'lastname',
        'order::increment_id'       => 'orderno',
        'order_status'              => 'orderstatus',
        'order::total_qty_ordered'  => 'totalqtyordered',
        'order::total_due'          => 'grandtotal',
        'product'                   => 'product',
        'order_url'                 => 'orderurl',
        'shipment_total_qty'        => 'totalqtyshipped',
        'track::track_number'       => 'trackingno',
        'track::title'              => 'provider',
        'store_name'                => 'storename',
        'store_address'             => 'storeaddress',
        'store_telephone'           => 'storecontacttelephone',
        'store_general_email'       => 'storegeneralcontactemail',
        'store_sales_email'         => 'salesrepemail',
        'store_support_email'       => 'customersupportemail',
        'store_custom_email_1'      => 'customemail1',
        'store_custom_email_2'      => 'customemail2',
        'store_url'                 => 'storeurl'
    );

    /**
     * We need to make sure the order has tracking & a phone number is available
     *
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     * @return bool
     */
    public function shouldSend(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        $order          = $this->parameters->getData('order');
        $shipment       = $this->parameters->getData('shipment');
        $trackingCodes  = $shipment->getAllTracks();
        $phoneNumber    = $order->getBillingAddress()->getTelephone();

        return (bool) count($trackingCodes) && $phoneNumber;
    }

    /**
     * Build container with available variables
     *
     * @return Varien_Object
     */
    public function getVariableContainer(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        /**
         * @var Mage_Sales_Model_Order_Shipment $shipment
         */
        $shipment = $this->parameters->getData('shipment');
        $this->parameters->setData('shipment_total_qty', intval($shipment->getData('total_qty')));
        $tracks = $shipment->getAllTracks();
        $this->parameters->setData('track', $tracks[0]);

        return parent::getVariableContainer($trigger);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return (int) $this->parameters->getData('order')->getData('store_id');
    }

    /**
     * Override to set the order as first class parameter
     * This is so we can call the parent::getVariableContainer to add all the default order variables
     * This expects that 'order' is present on the parameters object
     *
     * @param Varien_Object $parameters
     */
    public function setParameters(Varien_Object $parameters)
    {
        $this->parameters = $parameters;
        $this->parameters->setData('order', $this->parameters->getData('shipment')->getOrder());
    }
}

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
 * Class Esendex_Events_Model_EventProcessor_OrderAbstract
 * @author Michael Woodward <michael@wearejh.com>
 */
abstract class Esendex_Events_Model_EventProcessor_OrderAbstract extends Esendex_Sms_Model_EventProcessor_Abstract
{
    /**
     * @var array
     */
    protected $variables = array(
        'order::customer_firstname' => 'firstname',
        'order::customer_lastname'  => 'lastname',
        'order::increment_id'       => 'orderno',
        'total_qty_ordered'         => 'totalqtyordered',
        'order::grand_total'        => 'grandtotal',
        'order_status'              => 'orderstatus',
        'order_url'                 => 'orderurl',
        'product'                   => 'product',
        'store_name'                => 'storename',
        'store_address'             => 'storeaddress',
        'store_telephone'           => 'storecontacttelephone',
        'store_general_email'       => 'storegeneralcontactemail',
        'store_sales_email'         => 'salesrepemail',
        'store_support_email'       => 'customersupportemail',
        'store_custom_email_1'      => 'customemail1',
        'store_custom_email_2'      => 'customemail2',
        'store_url'                 => 'storeurl',
    );

    /**
     * Build container with available variables
     *
     * @return Varien_Object
     */
    public function getVariableContainer(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order      = $this->parameters->getData('order');
        $storeId    = $order->getStoreId();

        // Product array
        $products = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $products[] = $item->getProduct()->getName();
        }

        $storeName = Mage::getStoreConfig('general/store_information/name', $storeId)
            ? Mage::getStoreConfig('general/store_information/name', $storeId)
            : Mage::app()->getStore($storeId)->getName();

        $data = array(
            'order_status'         => $order->getStatusLabel(),
            'product'              => implode(', ', $products),
            'order_url'            => $this->getOrderUrl($order),
            'total_qty_ordered'    => intval($order->getData('total_qty_ordered')),
            'store_name'           => $storeName,
            'store_address'        => $this->getStoreConfig('general/store_information/address', $storeId),
            'store_telephone'      => $this->getStoreConfig('general/store_information/phone', $storeId),
            'store_general_email'  => $this->getStoreConfig('trans_email/ident_general/email', $storeId),
            'store_sales_email'    => $this->getStoreConfig('trans_email/ident_sales/email', $storeId),
            'store_support_email'  => $this->getStoreConfig('trans_email/ident_support/email', $storeId),
            'store_custom_email_1' => $this->getStoreConfig('trans_email/ident_custom1/email', $storeId),
            'store_custom_email_2' => $this->getStoreConfig('trans_email/ident_custom2/email', $storeId),
            'store_url'            => $this->getStoreUrl($storeId),
        );

        $this->parameters->addData($data);
        return $this->parameters;
    }

    /**
     * Get the order URL for a given order
     *
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    public function getOrderUrl($order)
    {
        $url = Mage::getModel('core/url')->getUrl('sales/order/view', array('order_id' => $order->getId())) ?: '';
        return $url;
    }

    /**
     * Get store base url
     *
     * @param int $storeId
     * @return string
     */
    public function getStoreUrl($storeId)
    {
        return Mage::app()->getStore($storeId)->getBaseUrl();
    }

    /**
     * Get config from path, allow for unit testing
     * Coerce null values to empty strings
     *
     * @param string $path
     * @param string $storeId
     * @return string|array
     */
    public function getStoreConfig($path, $storeId)
    {
        $v = Mage::getStoreConfig($path, $storeId);
        return $v ?: '';
    }

    /**
     * Get the recipients or recipient.
     *
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     * @return array|string
     */
    public function getRecipient(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $this->parameters->getData('order');
        return $order->getBillingAddress()->getTelephone();
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return (int) $this->parameters->getData('order')->getData('store_id');
    }
}
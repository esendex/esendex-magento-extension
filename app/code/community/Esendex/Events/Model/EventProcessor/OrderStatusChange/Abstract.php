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

use Psr\Log\LoggerInterface;

/**
 * Class Esendex_Events_Model_EventProcessor_OrderStatusChange_Abstract
 * @author Michael Woodward <michael@wearejh.com>
 */
abstract class Esendex_Events_Model_EventProcessor_OrderStatusChange_Abstract
    extends Esendex_Events_Model_EventProcessor_OrderAbstract
    implements Esendex_Sms_Model_EventProcessor_Interface,
    Esendex_Sms_Model_Logger_LoggerAwareInterface
{
    /**
     * Order status to notify on
     */
    protected $orderStatus = null;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Construct to enforce model has order status set
     */
    public function __construct()
    {
        if ($this->getOrderStatus() === null ) {
            throw new \RuntimeException(
                sprintf('Class "%s" must specify the protected property orderStatus', get_class($this))
            );
        }

        parent::__construct();
    }

    /**
     * @return string|null
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * If the status has changed and this new status is one
     * which has been set as one to notify the customer
     *
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     * @return bool
     */
    public function shouldSend(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $this->parameters->getData('order');

        if (!$order->dataHasChangedFor('status')) {
            return false;
        }

        if ($order->getData('status') !== $this->getOrderStatus()) {
            return false;
        }

        if (!$order->getBillingAddress()->getTelephone()) {
            return false;
        }

        return true;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
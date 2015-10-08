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
 * Class EventProcessor_OrderStatusChange_AbstractTest
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class EventProcessor_OrderStatusChange_AbstractTest extends PHPUnit_Framework_TestCase
{
    protected $event;
    protected $trigger;
    protected $order;
    protected $address;

    public function setUp()
    {
        $this->event = new CanceledTestAsset();

        $this->trigger = $this->getMockBuilder('Esendex_Sms_Model_TriggerAbstract')
            ->setMethods(array('getData'))
            ->getMock();

        $this->order = $this->getMockBuilder('Mage_Sales_Model_Order')
            ->setMethods(array('getBillingAddress', 'dataHasChangedFor', 'getData'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->address = $this->getMockBuilder('Mage_Sales_Model_Order_Address')
            ->setMethods(array('getTelephone'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->order
            ->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($this->address));
    }

    public function setOrder()
    {
        $parameters = new Varien_Object();
        $parameters->setData('order', $this->order);
        $this->event->setParameters($parameters);
    }

    public function testShouldSendReturnsTrueIfStatusHasChangedToCancelled()
    {
        $this->setOrder();

        $this->order
            ->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('status')
            ->will($this->returnValue(true));

        $this->order
            ->expects($this->once())
            ->method('getData')
            ->with('status')
            ->will($this->returnValue('canceled'));

        $this->address
            ->expects($this->once())
            ->method('getTelephone')
            ->will($this->returnValue('01231239'));

        $this->assertTrue($this->event->shouldSend($this->trigger));
    }

    public function testShouldSendReturnsFalseIfUserHasNoTelephoneSet()
    {
        $this->setOrder();

        $this->order
            ->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('status')
            ->will($this->returnValue(true));

        $this->order
            ->expects($this->once())
            ->method('getData')
            ->with('status')
            ->will($this->returnValue('canceled'));

        $this->address
            ->expects($this->once())
            ->method('getTelephone')
            ->will($this->returnValue(null));

        $this->assertFalse($this->event->shouldSend($this->trigger));
    }

    public function testShouldSendReturnsFalseIfStatusHasChangedButNotToCancelled()
    {
        $this->setOrder();

        $this->order
            ->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('status')
            ->will($this->returnValue(true));

        $this->order
            ->expects($this->once())
            ->method('getData')
            ->with('status')
            ->will($this->returnValue('closed'));

        $this->assertFalse($this->event->shouldSend($this->trigger));
    }

    public function testShouldSendReturnsFalseIfStatusHasNotChanged()
    {
        $this->setOrder();

        $this->order
            ->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('status')
            ->will($this->returnValue(false));

        $this->order
            ->expects($this->never())
            ->method('getData');

        $this->assertFalse($this->event->shouldSend($this->trigger));
    }

    public function testGetRecipientWithOrder()
    {
        $this->setOrder();

        $this->address
            ->expects($this->once())
            ->method('getTelephone')
            ->will($this->returnValue('0123123123'));

        $this->assertSame('0123123123', $this->event->getRecipient($this->trigger));
    }

    public function testGetRecipientWithOrderButNoNumber()
    {
        $this->setOrder();

        $this->address
            ->expects($this->once())
            ->method('getTelephone')
            ->will($this->returnValue(false));

        $this->assertFalse($this->event->getRecipient($this->trigger));
    }

    public function testSetAndGetLogger()
    {
        $logger = new \Psr\Log\NullLogger();
        $this->assertNull($this->event->getLogger());

        $this->event->setLogger($logger);
        $this->assertSame($logger, $this->event->getLogger());
    }

    public function testConstructorThrowsExceptionIfOrderStatusNotSet()
    {
        $eventProcessor = $this->getMockBuilder('Esendex_Events_Model_EventProcessor_OrderStatusChange_Abstract')
            ->setMethods(array('getOrderStatus'))
            ->disableOriginalConstructor()
            ->getMock();

        $eventProcessor->expects($this->once())
            ->method('getOrderStatus')
            ->will($this->returnValue(null));

        $this->setExpectedException('RuntimeException');
        $eventProcessor->__construct();
    }
}

class CanceledTestAsset extends Esendex_Events_Model_EventProcessor_OrderStatusChange_Abstract
{ protected $orderStatus = 'canceled'; }

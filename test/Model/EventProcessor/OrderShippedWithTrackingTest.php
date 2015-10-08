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
 * Class EventProcessor_OrderShippedWithTackingTest
 *
 * @author Michael Woodward <michael@wearejh.com>
 */
class EventProcessor_OrderShippedWithTrackingTest extends \PHPUnit_Framework_TestCase
{
    protected $event;
    protected $trigger;
    protected $order;
    protected $shipment;
    protected $address;

    public function setUp()
    {
        $this->event    = new Esendex_Events_Model_EventProcessor_OrderShippedWithTracking();
        $this->trigger  = new Esendex_Sms_Model_Trigger();

        $this->shipment = $this->getMockBuilder('Mage_Sales_Model_Order_Shipment')
            ->setMethods(array('getAllTracks', 'getOrder'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->order = $this->getMockBuilder('Mage_Sales_Model_Order')
            ->setMethods(array('getBillingAddress'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->address = $this->getMockBuilder('Mage_Sales_Model_Order_Address')
            ->setMethods(array('getData', 'getTrackingNumbers', 'getTelephone'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->order
            ->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($this->address));
    }

    /**
     * @dataProvider shouldSendDataProvider
     */
    public function testShouldSend($trackingNumbers, $telephone, $expected)
    {
        $this->shipment
            ->expects($this->once())
            ->method('getAllTracks', 'getData')
            ->will($this->returnValue($trackingNumbers));

        $this->address
            ->expects($this->once())
            ->method('getTelephone')
            ->will($this->returnValue($telephone));

        $this->shipment
            ->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($this->order));

        $this->event->setParameters(new Varien_Object(array(
            'shipment'  => $this->shipment,
        )));

        $this->assertSame($expected, $this->event->shouldSend($this->trigger));
    }

    public function shouldSendDataProvider()
    {
        return array(
            array(array('123123123', '2343453456'),   '011231231',    true),
            array(array('123123123', '2343453456'),   null,           false),
            array(array(),                            '011231231',    false),
            array(array(),                            null,           false)
        );
    }

    public function testGetRecipientWithOrder()
    {
        $this->shipment
            ->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($this->order));

        $parameters = new Varien_Object(array(
            'shipment' => $this->shipment
        ));
        $this->event->setParameters($parameters);

        $this->address
            ->expects($this->once())
            ->method('getTelephone')
            ->will($this->returnValue('0123123123'));

        $this->assertSame('0123123123', $this->event->getRecipient($this->trigger));
    }

    public function testGetRecipientWithOrderButNoNumber()
    {
        $this->shipment
            ->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($this->order));

        $parameters = new Varien_Object(array(
            'shipment' => $this->shipment
        ));
        $this->event->setParameters($parameters);

        $this->address
            ->expects($this->once())
            ->method('getTelephone')
            ->will($this->returnValue(false));

        $this->assertSame(false, $this->event->getRecipient($this->trigger));
    }
}

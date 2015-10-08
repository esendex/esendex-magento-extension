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
 * Class EventProcessor_AdminSalesReportTest
 *
 * @author Michael Woodward <michael@wearejh.com>
 */
class EventProcessor_AdminSalesReportTest extends \PHPUnit_Framework_TestCase
{
    protected $event;
    protected $trigger;

    public function setUp()
    {
        ini_set('display_errors', 1);
        $this->event    = new Esendex_Events_Model_EventProcessor_AdminSalesReport();
        $this->trigger  = $this->getMockBuilder('Esendex_Events_Model_AdminSalesReport')
            ->setMethods(array('getData', 'lookupStoreIds'))
            ->getMock();
    }

    /**
     * @param $currentDate
     * @param $date
     * @param $frequency
     * @param $expected
     * @param $willLog
     * @dataProvider shouldSendProvider
     */
    public function testShouldSend($currentDate, $date, $frequency, $expected, $willLog)
    {
        $this->trigger
            ->expects($this->any())
            ->method('getData', 'lookupStoreIds')
            ->will($this->onConsecutiveCalls($date, $frequency));
        
        if ($willLog) {
            $logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
                ->setMethods(array('critical'))
                ->getMockForAbstractClass();

            $this->event->setLogger($logger);

            $logger
                ->expects($this->once())
                ->method('critical');
        }

        $this->event->setCurrentDate($currentDate);

        $this->assertSame($expected, $this->event->shouldSend($this->trigger));
    }

    public function shouldSendProvider()
    {
        return array(
            array('2015-02-28 00:00:00', '2015-01-31 00:00:00',  'monthly',   true,      false),
            array('2014-11-15 00:00:00', '2014-11-08 00:00:00',  'daily',     true,      false),
            array('2014-11-15 00:00:00', '2014-11-08 00:00:00',  'weekly',    true,      false),
            array('2014-12-08 00:00:00', '2014-11-08 00:00:00',  'monthly',   true,      false),
            array('2015-11-08 00:00:00', '2014-11-08 00:00:00',  'monthly',   true,      false),
            array('2014-11-07 00:00:00', '2014-11-08 00:00:00',  'daily',     false,     false),
            array('2014-11-16 00:00:00', '2014-11-08 00:00:00',  'weekly',    false,     false),
            array('2015-12-15 00:00:00', '2014-11-08 00:00:00',  'monthly',   false,     false),
            array('2014-11-15 00:00:00', 'BAD DATE STRING',      'monthly',   false,     true),
        );
    }

    public function getOrderResourceCollection()
    {
        $orderResourceCollection = $this
            ->getMockBuilder('Esendex_Events_Model_Resource_AdminSalesReport_Order_Collection')
            ->disableOriginalConstructor()
            ->setMethods(array('calculateTotals', 'addFieldToFilter', 'getFirstItem'))
            ->getMock();

        $orderResourceCollection
            ->expects($this->any())
            ->method('calculateTotals')
            ->will($this->returnValue($orderResourceCollection));

        return $orderResourceCollection;
    }

    /**
     * @param $frequency
     * @param $storeIds
     * @param $expectedData
     * @dataProvider getVariableContainerProvider
     */
    public function testGetVariableContainer($frequency, $storeIds, $expectedData)
    {
        $this->trigger
            ->expects($this->at(0))
            ->method('getData')
            ->with('frequency')
            ->will($this->returnValue($frequency));

        $this->trigger
            ->expects($this->at(1))
            ->method('getData')
            ->with('store_id')
            ->will($this->returnValue($storeIds));

        $event = $this->getMockBuilder('Esendex_Events_Model_EventProcessor_AdminSalesReport')
            ->setMethods(array('getReportTotals'))
            ->getMock();

        $event
            ->expects($this->any())
            ->method('getReportTotals')
            ->will($this->returnValue(new Varien_Object($expectedData)));

        $container = $event->getVariableContainer($this->trigger);

        $this->assertSame($expectedData['net'], $container->getData('net_total'));
        $this->assertSame($expectedData['gross'], $container->getData('gross_total'));
        $this->assertSame($expectedData['total_orders'], $container->getData('total_sales'));
        $this->assertSame(number_format($expectedData['total_items'], 2), $container->getData('qty_items_sold'));
    }

    /**
     * Data Provider for testGetVariableContainer
     *
     * @return array
     */
    public function getVariableContainerProvider()
    {
        return array(
            array(
                'freq'         => 'daily',
                'store_ids'    => array(0),
                'expectedData' => array(
                    'net'          => '$0.00',
                    'gross'        => '$0.00',
                    'total_orders' => 1,
                    'total_items'  => 2
                )
            ),
            array(
                'freq'         => 'weekly',
                'store_ids'    => array(0),
                'expectedData' => array(
                    'net'          => '$0.00',
                    'gross'        => '$0.00',
                    'total_orders' => 1,
                    'total_items'  => 2
                )
            ),
            array(
                'freq'         => 'monthly',
                'store_ids'    => array(0),
                'expectedData' => array(
                    'net'          => '$0.00',
                    'gross'        => '$0.00',
                    'total_orders' => 1,
                    'total_items'  => 2
                )
            ),
            array(
                'freq'         => 'monthly',
                'store_ids'    => array(0),
                'expectedData' => array(
                    'net'          => '$0.00',
                    'gross'        => '$0.00',
                    'total_orders' => 1,
                    'total_items'  => 2
                )
            ),
        );
    }

    public function testGetVariableContainerWillUseParameterData()
    {
        $this->trigger
            ->expects($this->at(0))
            ->method('getData')
            ->with('frequency')
            ->will($this->returnValue('daily'));

        $this->trigger
            ->expects($this->at(1))
            ->method('getData')
            ->with('store_id')
            ->will($this->returnValue(array(1)));

        $event = $this->getMockBuilder('Esendex_Events_Model_EventProcessor_AdminSalesReport')
            ->setMethods(array('getReportTotals'))
            ->getMock();

        $event
            ->expects($this->any())
            ->method('getReportTotals')
            ->will($this->returnValue(new Varien_Object(array(
                'net_total'    => '$0.00',
                'gross_total'  => '$0.00',
                'total_orders' => 1,
                'total_items'  => 1.00,
            ))));

        $parameters = new Varien_Object(array('default_data'  => 'test'));
        $event->setParameters($parameters);

        $container = $event->getVariableContainer($this->trigger);

        $this->assertSame('$0.00', $container->getData('net_total'));
        $this->assertSame('$0.00', $container->getData('gross_total'));
        $this->assertSame(1, $container->getData('total_sales'));
        $this->assertSame(number_format(1, 2), $container->getData('qty_items_sold'));

        // Ensure original parameter data still exists
        $this->assertSame('test', $container->getData('default_data'));
    }

    /**
     * @param string $currentDate
     * @param string $timezone
     * @param int    $firstDay
     * @param string $frequency
     * @param string $expectedTo
     * @param string $expectedFrom
     * @dataProvider getSetPeriodDateByFrequencyProvider
     */
    public function testSetPeriodDateByFrequency(
        $currentDate,
        $timezone,
        $firstDay,
        $frequency,
        $expectedFrom,
        $expectedTo
    )
    {
        // Set test data
        $this->event->setCurrentDate($currentDate);
        $this->event->setTimezone($timezone);
        $this->event->setFirstDay($firstDay);

        // Function is protected so use reflection
        $method = new \ReflectionMethod($this->event, 'setPeriodDateByFrequency');
        $method->setAccessible(true);

        $method->invoke($this->event, $frequency);

        $this->assertSame($expectedFrom, $this->event->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertSame($expectedTo, $this->event->getEndDate()->format('Y-m-d H:i:s'));
    }

    public function getSetPeriodDateByFrequencyProvider()
    {
        return array(
            // GMT Europe/London
            // Daily
            array('2015-03-01 00:00:00', 'Europe/London', 0, 'daily',  '2015-02-28 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-01 09:00:00', 'Europe/London', 0, 'daily',  '2015-02-28 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-01 23:59:00', 'Europe/London', 0, 'daily',  '2015-02-28 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-02 16:00:00', 'Europe/London', 0, 'daily',  '2015-03-01 00:00:00', '2015-03-01 23:59:59'),
            // Weekly
            array('2015-03-01 00:00:00', 'Europe/London', 1, 'weekly', '2015-02-16 00:00:00', '2015-02-22 23:59:59'),
            array('2015-03-01 09:00:00', 'Europe/London', 1, 'weekly', '2015-02-16 00:00:00', '2015-02-22 23:59:59'),
            array('2015-03-01 23:59:00', 'Europe/London', 1, 'weekly', '2015-02-16 00:00:00', '2015-02-22 23:59:59'),
            array('2015-03-01 00:00:00', 'Europe/London', 0, 'weekly', '2015-02-22 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-01 09:00:00', 'Europe/London', 0, 'weekly', '2015-02-22 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-01 23:59:00', 'Europe/London', 0, 'weekly', '2015-02-22 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-04 00:00:00', 'Europe/London', 1, 'weekly', '2015-02-23 00:00:00', '2015-03-01 23:59:59'),
            array('2015-03-04 00:00:00', 'Europe/London', 0, 'weekly', '2015-02-22 00:00:00', '2015-02-28 23:59:59'),
            // Monthly
            array('2015-03-01 00:00:00', 'Europe/London', 0, 'monthly', '2015-02-01 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-01 09:00:00', 'Europe/London', 0, 'monthly', '2015-02-01 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-01 23:59:00', 'Europe/London', 0, 'monthly', '2015-02-01 00:00:00', '2015-02-28 23:59:59'),
            array('2015-02-28 00:00:00', 'Europe/London', 0, 'monthly', '2015-01-01 00:00:00', '2015-01-31 23:59:59'),
            array('2015-03-04 09:00:00', 'Europe/London', 0, 'monthly', '2015-02-01 00:00:00', '2015-02-28 23:59:59'),
            // EST America/New_York
            // Daily
            array('2015-03-01 00:00:00', 'America/New_York', 0, 'daily',  '2015-02-28 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-01 09:00:00', 'America/New_York', 0, 'daily',  '2015-02-28 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-01 23:59:00', 'America/New_York', 0, 'daily',  '2015-02-28 00:00:00', '2015-02-28 23:59:59'),
            // Weekly
            array('2015-03-01 00:00:00', 'America/New_York', 1, 'weekly', '2015-02-16 00:00:00', '2015-02-22 23:59:59'),
            array('2015-03-01 09:00:00', 'America/New_York', 1, 'weekly', '2015-02-16 00:00:00', '2015-02-22 23:59:59'),
            array('2015-03-01 23:59:00', 'America/New_York', 1, 'weekly', '2015-02-16 00:00:00', '2015-02-22 23:59:59'),
            // Monthly
            array('2015-03-01 00:00:00', 'America/New_York', 0, 'monthly', '2015-02-01 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-01 09:00:00', 'America/New_York', 0, 'monthly', '2015-02-01 00:00:00', '2015-02-28 23:59:59'),
            array('2015-03-01 23:59:00', 'America/New_York', 0, 'monthly', '2015-02-01 00:00:00', '2015-02-28 23:59:59'),
        );
    }

    public function testGetReportTotalsSetsCorrectDataOnCollection()
    {
        $startDate = new \DateTime();
        $endDate   = clone $startDate;

        $startDate->setTime(0,0);
        $endDate->setTime(23,59);

        // Use the mock order resource collection
        $orderResourceCollection = $this->getOrderResourceCollection();

        $orderResourceCollection
            ->expects($this->at(0))
            ->method('addFieldToFilter')
            ->with('created_at', array(
                'from' => $startDate->format(Varien_Date::DATETIME_PHP_FORMAT),
                'to'   => $endDate->format(Varien_Date::DATETIME_PHP_FORMAT)
            ))
            ->will($this->returnSelf());

        $orderResourceCollection
            ->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('store_id', 1)
            ->will($this->returnSelf());

        $orderResourceCollection
            ->expects($this->once())
            ->method('calculateTotals')
            ->will($this->returnSelf());

        $orderResourceCollection
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue(new Varien_Object(array(
                'net_total'    => '$0.00',
                'gross_total'  => '$0.00',
                'total_orders' => 1,
                'total_items'  => 1.00,
            ))));

        // Mock the event to inject order resource collection
        $event = $this->getMockBuilder('Esendex_Events_Model_EventProcessor_AdminSalesReport')
            ->setMethods(array('getOrderResourceCollection'))
            ->getMock();

        $event
            ->expects($this->once())
            ->method('getOrderResourceCollection')
            ->will($this->returnValue($orderResourceCollection));

        // Protected method, use reflection
        $method = new \ReflectionMethod($event, 'getReportTotals');
        $method->setAccessible(true);

        $result = $method->invoke($event, $startDate, $endDate, 1);

        $this->assertSame('$0.00', $result->getData('net_total'));
    }

    public function testGetRecipientWithRecipients()
    {
        $numbers = array(
            '0123123123',
            '0123123124',
            '0123123125'
        );

        $this->trigger
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($numbers));

        $this->assertSame($numbers, $this->event->getRecipient($this->trigger));
    }

    public function testGetRecipientWithoutRecipients()
    {
        $this->trigger
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(false));

        $this->assertSame(array(), $this->event->getRecipient($this->trigger));
    }

    public function testSetAndGetLogger()
    {
        $logger = new \Psr\Log\NullLogger();

        $this->assertNull($this->event->getLogger());

        $this->event->setLogger($logger);

        $this->assertSame($logger, $this->event->getLogger());
    }
}
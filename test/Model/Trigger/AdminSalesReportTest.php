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
 * Class Trigger_AdminSalesReport
 *
 * @author Michael Woodward <michael@wearejh.com>
 */
class Trigger_AdminSalesReportTest extends \PHPUnit_Framework_TestCase
{
    protected $trigger;

    public function setUp()
    {
        $this->trigger = new Esendex_Events_Model_AdminSalesReport();
    }

    public function testValidatesValidNumbers()
    {
        $validNumbers = array(
            '01223887652',
            '01233887476'
        );

        $this->trigger->setData('recipients', implode("\n\r", $validNumbers));

        $result = $this->trigger->validate();

        $this->assertTrue($result);
        $this->assertSame($validNumbers, $this->trigger->getData('recipients'));
    }

    public function testRemovesDuplicateNumbers()
    {
        $validNumbers = array(
            '01223887652',
            '01223887652'
        );

        $this->trigger->setData('recipients', implode("\n\r", $validNumbers));

        $result = $this->trigger->validate();

        $this->assertTrue($result);
        $this->assertSame(array('01223887652'), $this->trigger->getData('recipients'));
    }

    public function testAddsErrorOnInvalidNumberInput()
    {
        $invalidNumbers = array(false);

        $trigger = $this->getMockBuilder('Esendex_Events_Model_AdminSalesReport')
            ->setMethods(array('addError', 'hasErrors'))
            ->getMock();

        $trigger
            ->expects($this->once())
            ->method('addError')
            ->with('Recipients cannot be empty');

        $trigger
            ->expects($this->any())
            ->method('hasErrors')
            ->will($this->returnValue(true));

        $trigger->setData('recipients', implode("\n\r", $invalidNumbers));

        $this->assertFalse($trigger->validate());
    }

    public function testAddsErrorOnBadDateInput()
    {
        $invalidNumbers = array('01223887652');

        $trigger = $this->getMockBuilder('Esendex_Events_Model_AdminSalesReport')
            ->setMethods(array('addError', 'hasErrors'))
            ->getMock();

        $trigger
            ->expects($this->once())
            ->method('addError')
            ->with('Invalid Start Date');

        $trigger
            ->expects($this->any())
            ->method('hasErrors')
            ->will($this->returnValue(true));

        $trigger->setData('recipients', implode("\n\r", $invalidNumbers));
        $trigger->setData('start_date', 'BAD DATE STRING :(');

        $this->assertFalse($trigger->validate());
    }

    public function testAddsErrorOnValidDateFormatButInvalidDate()
    {
        $invalidNumbers = array('01223887652');

        $trigger = $this->getMockBuilder('Esendex_Events_Model_AdminSalesReport')
            ->setMethods(array('addError', 'hasErrors'))
            ->getMock();

        $trigger
            ->expects($this->once())
            ->method('addError')
            ->with('Invalid Start Date');

        $trigger
            ->expects($this->any())
            ->method('hasErrors')
            ->will($this->returnValue(true));

        $trigger->setData('recipients', implode("\n\r", $invalidNumbers));
        $trigger->setData('start_date', 'February 30, 2070');

        $this->assertFalse($trigger->validate());
    }

    public function testSetsCorrectDate()
    {
        $validNumbers = array('01223887652');

        $this->trigger->setData('recipients', implode("\n\r", $validNumbers));
        $this->trigger->setData('start_date', 'January 1, 2070');

        $result = $this->trigger->validate();

        $expectedDate  = new \DateTime('01/01/2070');
        $expDateString = $expectedDate->format(Varien_Date::DATETIME_PHP_FORMAT);

        $this->assertTrue($result);
        $this->assertSame($expDateString, $this->trigger->getData('start_date'));
    }
}

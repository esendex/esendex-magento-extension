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
 * Class MessageInterpolatorTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MessageInterpolatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Esendex_Sms_Model_MessageInterpolator
     */
    protected $interpolator;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    public function setUp()
    {
        $this->logger       = $this->getMock('Psr\Log\LoggerInterface');
        $this->interpolator = new Esendex_Sms_Model_MessageInterpolator($this->logger);
    }

    public function testVariablesAreInterpolated()
    {
        $variables = [
            new Esendex_Sms_Model_Variable('name', 'order::customer_name'),
        ];

        $container  = new Varien_Object();
        $order      = new Varien_Object();
        $order->setData('customer_name', 'Aydin Hassan');
        $container->setData('order', $order);

        $message    = 'Hello $NAME$ - SUP?';
        $result     = $this->interpolator->interpolate($message, $container, $variables);
        $expected   = 'Hello Aydin Hassan - SUP?';

        $this->assertSame($expected, $result);
    }

    public function testNestedVariablesAreInterpolated()
    {
        $variables = [
            new Esendex_Sms_Model_Variable('name', 'order::shipment::customer_name'),
        ];

        $container  = new Varien_Object();
        $order      = new Varien_Object();
        $shipment   = new Varien_Object();
        $shipment->setData('customer_name', 'Aydin Hassan');
        $order->setData('shipment', $shipment);
        $container->setData('order', $order);

        $message    = 'Hello $NAME$ - SUP?';
        $result     = $this->interpolator->interpolate($message, $container, $variables);
        $expected   = 'Hello Aydin Hassan - SUP?';

        $this->assertSame($expected, $result);
    }

    public function testPlaceHolderIsLeftIfVariableCannotBeFound()
    {
        $variables = [
            new Esendex_Sms_Model_Variable('name', 'order::shipment::customer_name'),
        ];

        $container  = new Varien_Object();
        $order      = new Varien_Object();
        $container->setData('order', $order);

        $msg = 'Could not find variable: "$NAME$" with path: "shipment" on object Varien_Object';
        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with($msg);

        $message    = 'Hello $NAME$ - SUP?';
        $result     = $this->interpolator->interpolate($message, $container, $variables);
        $expected   = 'Hello - SUP?';

        $this->assertSame($expected, $result);
    }

    public function missingVariableProvider()
    {
        return array(
            array('Hello $FIRSTNAME$ $LASTNAME$ - SUP?',   'Hello Aydin - SUP?'),
            array('Hello $FIRSTNAME$$LASTNAME$ - SUP?',   'Hello Aydin - SUP?'),
            array('Hello $FIRSTNAME$    $LASTNAME$ - SUP?',   'Hello Aydin    - SUP?'),
        );
    }

    /**
     * @dataProvider missingVariableProvider
     *
     * @param string $message
     * @param string $expected
     */
    public function testPlaceHolderIsRemovedIncludingOptionalPrefixedSpaceIfVariableDoesNotExist($message, $expected)
    {
        $variables = [
            new Esendex_Sms_Model_Variable('firstname', 'order::first_name'),
            new Esendex_Sms_Model_Variable('lastname', 'order::last_name'),
        ];

        $container  = new Varien_Object();
        $order      = new Varien_Object(array(
            'first_name' => 'Aydin',
        ));
        $container->setData('order', $order);

        $msg = 'Could not find variable: "$LASTNAME$" with path: "last_name" on object Varien_Object';
        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with($msg);

        $result = $this->interpolator->interpolate($message, $container, $variables);
        $this->assertSame($expected, $result);
    }

    public function testPlaceHolderIsRemovedIfVariableCannotBeFoundAtTheEndOfThePath()
    {
        $variables = [
            new Esendex_Sms_Model_Variable('name', 'order::customer_name'),
        ];

        $container  = new Varien_Object();
        $order      = new Varien_Object();
        $container->setData('order', $order);

        $msg = 'Could not find variable: "$NAME$" with path: "customer_name" on object Varien_Object';
        $this->logger
            ->expects($this->once())
            ->method('debug')
            ->with($msg);

        $message    = 'Hello $NAME$ - SUP?';
        $result     = $this->interpolator->interpolate($message, $container, $variables);
        $expected   = 'Hello - SUP?';

        $this->assertSame($expected, $result);
    }

    public function testMultipleVariablesAreReplaced()
    {
        $variables = [
            new Esendex_Sms_Model_Variable('name', 'order::customer::customer_name'),
            new Esendex_Sms_Model_Variable('ordernum', 'order::number'),
        ];

        $container  = new Varien_Object();
        $order      = new Varien_Object();
        $customer   = new Varien_Object();
        $customer->setData('customer_name', 'Aydin Hassan');
        $order->setData([
            'number'    => '3124',
            'customer'  => $customer,
        ]);
        $container->setData('order', $order);

        $message    = 'Hello $NAME$ - Order #$ORDERNUM$ has shipped';
        $result     = $this->interpolator->interpolate($message, $container, $variables);
        $expected   = 'Hello Aydin Hassan - Order #3124 has shipped';

        $this->assertSame($expected, $result);
    }

    public function testNonNestedVariablesAreReplaced()
    {
        $variables = [
            new Esendex_Sms_Model_Variable('totalsales', 'total_sales'),
            new Esendex_Sms_Model_Variable('numorders', 'number_of_orders'),
        ];

        $container = new Varien_Object();
        $container->setData([
            'total_sales'       => '£200',
            'number_of_orders'  => 100,
        ]);

        $message    = 'Your store has made $TOTALSALES$ through $NUMORDERS$ orders';
        $result     = $this->interpolator->interpolate($message, $container, $variables);
        $expected   = 'Your store has made £200 through 100 orders';

        $this->assertSame($expected, $result);
    }

    public function testNonScalarReplaceValuesAreNotInterpolated()
    {
        $variables = [
            new Esendex_Sms_Model_Variable('totalsales', 'total_sales'),
            new Esendex_Sms_Model_Variable('numorders', 'number_of_orders'),
        ];

        $container = new Varien_Object();
        $container->setData([
            'total_sales'       => new stdClass,
            'number_of_orders'  => 100,
        ]);

        $msg = 'Cannot replace placeholder with a non-scalar value (Eg, String, Integer). Got: "stdClass"';
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($msg);

        $message    = 'Your store has made $TOTALSALES$ through $NUMORDERS$ orders';
        $result     = $this->interpolator->interpolate($message, $container, $variables);
        $expected   = 'Your store has made through 100 orders';

        $this->assertSame($expected, $result);
    }
}

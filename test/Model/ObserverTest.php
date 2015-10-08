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
 * Class ObserverTest
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    protected $esendexApi;
    protected $messageProcessor;
    protected $observer;

    public function setUp()
    {
        // Mock the Esendex API Class
        $this->esendexApi = $this->getMockBuilder('Esendex_Sms_Model_Api_Api')
            ->setMethods(['sendMultipleMessages'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageProcessor = $this->getMockBuilder('Esendex_Sms_Model_MessageProcessor_MessageProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = $this->getMockBuilder('Esendex_Sms_Model_Observer')
            ->setMethods(array('getEvents'))
            ->getMock();

        $this->setProtectedProperty($this->observer, 'messageProcessor', $this->messageProcessor);
        $this->setProtectedProperty($this->observer, 'esendexApi', $this->esendexApi);

        $this->assertSame($this->esendexApi, $this->observer->getEsendexApi());
        $this->assertSame($this->messageProcessor, $this->observer->getMessageProcessor());
    }

    /**
     * @param mixed $object
     * @param string $property
     * @param mixed $value
     */
    public function setProtectedProperty($object, $property, $value)
    {
        $reflector = new ReflectionClass($object);
        $property = $reflector->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    public function testDispatchEventCallsApiWithMessages()
    {
        $e = new Varien_Event();
        $e->setName('triggerCode');
        $observer = new Varien_Event_Observer();
        $observer->setEvent($e);
        $events = $this->getEventCollection();

        $this->observer
            ->expects($this->once())
            ->method('getEvents')
            ->with('event', 'triggerCode')
            ->will($this->returnValue($events));

        $messages = array(
            new Esendex_Sms_Model_Message('I wish I could write as mysterious as a cat.', 'Edgar', '0123455')
        );

        $this->messageProcessor
            ->expects($this->once())
            ->method('processEvents')
            ->with($events, $observer)
            ->will($this->returnValue($messages));

        $this->esendexApi
            ->expects($this->once())
            ->method('sendMultipleMessages')
            ->with($messages);

        $this->observer->dispatchEvent($observer);
    }

    public function testDispatchEventDoesNotCallApiIfNoMessages()
    {
        $e = new Varien_Event();
        $e->setName('triggerCode');
        $observer = new Varien_Event_Observer();
        $observer->setEvent($e);
        $events = $this->getEventCollection();

        $this->observer
            ->expects($this->once())
            ->method('getEvents')
            ->with('event', 'triggerCode')
            ->will($this->returnValue($events));

        $messages = array();

        $this->messageProcessor
            ->expects($this->once())
            ->method('processEvents')
            ->with($events, $observer)
            ->will($this->returnValue($messages));

        $this->esendexApi
            ->expects($this->never())
            ->method('sendMultipleMessages');

        $this->observer->dispatchEvent($observer);
    }

    public function testDispatchCronCallsApiWithMessages()
    {
        $schedule = new Mage_Cron_Model_Schedule();
        $schedule->setJobCode('triggerCode');
        $events = $this->getEventCollection();

        $this->observer
            ->expects($this->once())
            ->method('getEvents')
            ->with('cron', 'triggerCode')
            ->will($this->returnValue($events));

        $messages = array(
            new Esendex_Sms_Model_Message('I wish I could write as mysterious as a cat.', 'Edgar', '0123455')
        );

        $this->messageProcessor
            ->expects($this->once())
            ->method('processEvents')
            ->with($events)
            ->will($this->returnValue($messages));

        $this->esendexApi
            ->expects($this->once())
            ->method('sendMultipleMessages')
            ->with($messages);

        $this->observer->dispatchCron($schedule);
    }

    public function testDispatchCronDoesNotCallApiIfNoMessages()
    {
        $schedule = new Mage_Cron_Model_Schedule();
        $schedule->setJobCode('triggerCode');
        $events = $this->getEventCollection();

        $this->observer
            ->expects($this->once())
            ->method('getEvents')
            ->with('cron', 'triggerCode')
            ->will($this->returnValue($events));

        $messages = array();

        $this->messageProcessor
            ->expects($this->once())
            ->method('processEvents')
            ->with($events)
            ->will($this->returnValue($messages));

        $this->esendexApi
            ->expects($this->never())
            ->method('sendMultipleMessages');

        $this->observer->dispatchCron($schedule);
    }

    private function getEventCollection()
    {
        $eventCollection = $this->getMockBuilder('Esendex_Sms_Model_Resource_Event_Collection')
            ->disableOriginalConstructor()
            ->setMethods(array('getIterator'))
            ->getMock();

        return $eventCollection;
    }


}
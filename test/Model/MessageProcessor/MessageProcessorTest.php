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
 * Class MessageProcessorTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MessageProcessorTest extends PHPUnit_Framework_TestCase
{
    protected $messageProcessor;
    protected $logger;
    protected $messageInterpolator;

    public function setUp()
    {
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->messageInterpolator = $this->getMockBuilder('Esendex_Sms_Model_MessageInterpolator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageProcessor = $this->getMockBuilder('Esendex_Sms_Model_MessageProcessor_MessageProcessor')
            ->setConstructorArgs(array($this->logger, $this->messageInterpolator))
            ->setMethods(array('getTriggers'))
            ->getMock();
    }

    public function testDispatchLogsAndReturnsIfEventProcessorCannotBeFound()
    {
        $event = $this->getMock('Esendex_Sms_Model_Event');
        $event->expects($this->once())
            ->method('getEventProcessor')
            ->will($this->throwException(new \RuntimeException('nope')));

        $collection = $this->getEventCollection(array($event));

        $this->logger->expects($this->once())
            ->method('critical')
            ->with('nope');

        $this->messageProcessor->processEvents($collection);
    }

    public function testMessagesAreProcessed()
    {
        $eventProcessor = $this->getMock('Esendex_Sms_Model_EventProcessor_Interface');
        $event = $this->getMock('Esendex_Sms_Model_Event');
        $event->expects($this->once())
            ->method('getEventProcessor')
            ->will($this->returnValue($eventProcessor));

        $collection = $this->getEventCollection(array($event));

        $trigger = $this->getMock('Esendex_Sms_Model_TriggerAbstract');
        $trigger->expects($this->once())
            ->method('getData')
            ->with('sender')
            ->will($this->returnValue('Edgar'));

        $triggers = $this->getTriggerCollection(array($trigger));

        $this->messageProcessor
            ->expects($this->once())
            ->method('getTriggers')
            ->with($event, null)
            ->will($this->returnValue($triggers));

        $eventProcessor->expects($this->once())
            ->method('shouldSend')
            ->with($trigger)
            ->will($this->returnValue(true));

        $eventProcessor->expects($this->once())
            ->method('getRecipient')
            ->with($trigger)
            ->will($this->returnValue('0123456789'));

        $message = "I became insane, with long intervals of horrible sanity.";

        $this->messageInterpolator
            ->expects($this->once())
            ->method('interpolate')
            ->will($this->returnValue($message));

        $eventProcessor->expects($this->once())
            ->method('postProcess')
            ->with($message)
            ->will($this->returnValue($message));

        $messages = $this->messageProcessor->processEvents($collection);

        $messageObj = $messages[0];
        $this->assertInternalType('array', $messages);
        $this->assertInstanceOf('Esendex_Sms_Model_Message', $messageObj);
        $this->assertSame($message, $messageObj->getMessageBody());
        $this->assertSame(array('0123456789'), $messageObj->getRecipients());
        $this->assertSame('Edgar', $messageObj->getSender());
    }

    public function testLoggerIsSetOnProcessorIfImplementsLoggerAware()
    {
        $eventProcessor = $this->getMock('EventProcessorWithLogger');
        $event = $this->getMock('Esendex_Sms_Model_Event');
        $event->expects($this->once())
            ->method('getEventProcessor')
            ->will($this->returnValue($eventProcessor));

        $collection = $this->getEventCollection(array($event));
        $trigger = $this->getMock('Esendex_Sms_Model_TriggerAbstract');
        $triggers = $this->getTriggerCollection(array($trigger));

        $this->messageProcessor
            ->expects($this->once())
            ->method('getTriggers')
            ->with($event, null)
            ->will($this->returnValue($triggers));

        $eventProcessor->expects($this->once())
            ->method('setLogger')
            ->with($this->logger)
            ->will($this->returnSelf());

        $this->messageProcessor->processEvents($collection);
    }

    public function testParametersAreSetIfNotNull()
    {
        $params = new Varien_Object();
        $eventProcessor = $this->getMock('Esendex_Sms_Model_EventProcessor_Interface');
        $event = $this->getMock('Esendex_Sms_Model_Event');
        $event->expects($this->once())
            ->method('getEventProcessor')
            ->will($this->returnValue($eventProcessor));

        $collection = $this->getEventCollection(array($event));
        $trigger = $this->getMock('Esendex_Sms_Model_TriggerAbstract');
        $triggers = $this->getTriggerCollection(array($trigger));

        $this->messageProcessor
            ->expects($this->once())
            ->method('getTriggers')
            ->with($event, null)
            ->will($this->returnValue($triggers));

        $eventProcessor->expects($this->once())
            ->method('setParameters')
            ->with($params);

        $this->messageProcessor->processEvents($collection, $params);
    }

    private function getEventCollection(array $events)
    {
        $eventCollection = $this->getMockBuilder('Esendex_Sms_Model_Resource_Event_Collection')
            ->disableOriginalConstructor()
            ->setMethods(array('getIterator'))
            ->getMock();

        $eventCollection
            ->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new ArrayIterator($events)));

        return $eventCollection;
    }

    private function getTriggerCollection(array $triggers)
    {
        $triggerCollection = $this->getMockBuilder('Esendex_Sms_Model_Resource_Trigger_Collection')
            ->disableOriginalConstructor()
            ->setMethods(array('getIterator'))
            ->getMock();

        $triggerCollection
            ->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new ArrayIterator($triggers)));

        return $triggerCollection;
    }

}

interface EventProcessorWithLogger extends
    Esendex_Sms_Model_Logger_LoggerAwareInterface,
    Esendex_Sms_Model_EventProcessor_Interface {}
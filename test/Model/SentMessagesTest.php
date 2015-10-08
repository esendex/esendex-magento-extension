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

use Esendex\Exceptions\EsendexException;
use Esendex\Model\SentMessagesPage;
use Esendex\Model\SentMessage;

/**
 * Class SentMessagesTest
 *
 * @author Michael Woodward <michael@wearejh.com>
 */
class SentMessagesTest extends \PHPUnit_Framework_TestCase
{
    protected $collection;
    protected $grid;
    protected $esendexApi;

    public function setUp()
    {
        $this->collection   = new Esendex_Sms_Model_SentMessages();

        // Mock the Esendex API Class
        $this->esendexApi = $this->getMockBuilder('Esendex_Sms_Model_Api_Api')
            ->setMethods(['getSentMessages'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->collection->setEsendexApi($this->esendexApi);

        // Set up the grid with collection containing mock API
        $this->grid = new Esendex_Sms_Block_Adminhtml_Messages_Grid();
        $this->grid->setCollection($this->collection);
    }

    public function newMessage($summary)
    {
        $message = new SentMessage();
        $message->summary($summary);
        $message->type('SMS');
        $message->originator('Unit Test');
        $message->recipient('00000000000');
        $message->status('Delivered');

        return $message;
    }

    public function testNewCollectionHasApi()
    {
        $this->assertInstanceOf('Esendex_Sms_Model_Api_Api', $this->collection->getEsendexApi());
    }

    public function testCollectionSetWithApiMock()
    {
        $this->assertAttributeSame($this->esendexApi, 'esendexApi', $this->collection);
    }

    public function testGridCollectionSetWithApiMock()
    {
        $this->assertSame($this->collection, $this->grid->getCollection());
        $this->assertAttributeSame($this->esendexApi, 'esendexApi', $this->grid->getCollection());
    }

    public function testCollectionGetsTotalSize()
    {
        $firstApiCall = new SentMessagesPage(0, 200);

        $this->esendexApi
            ->expects($this->any())
            ->method('getSentMessages')
            ->will($this->returnValue($firstApiCall));

        $this->collection->loadData();

        $this->assertEquals(200, $this->collection->getSize());
    }

    public function testCollectionSetsMessages()
    {
        $firstApiCall   = new SentMessagesPage(0, 200);
        $secondApiCall  = [];

        foreach (range(1, 200) as $i) {
            $secondApiCall[] = $this->newMessage('This is message ' . $i);
        }

        $this->esendexApi
            ->expects($this->exactly(2))
            ->method('getSentMessages')
            ->will($this->onConsecutiveCalls($firstApiCall, $secondApiCall));

        $this->assertEquals(200, count($this->collection->getItems()));
    }

    public function testCollectionSetsMessagesAsVarienObjects()
    {
        $firstApiCall   = new SentMessagesPage(0, 1);
        $secondApiCall  = [$this->newMessage('This is a message')];

        $this->esendexApi
            ->expects($this->exactly(2))
            ->method('getSentMessages')
            ->will($this->onConsecutiveCalls($firstApiCall, $secondApiCall));

        $this->assertEquals(1, count($this->collection->getItems()));
        $this->assertInstanceOf('Varien_Object', $this->collection->getFirstItem());

        $firstItem = $this->collection->getFirstItem();

        $this->assertEquals('SMS', $firstItem->getType());
        $this->assertEquals('Unit Test', $firstItem->getOriginator());
        $this->assertEquals('00000000000', $firstItem->getRecipient());
        $this->assertEquals('Delivered', $firstItem->getStatus());
        $this->assertEquals('This is a message', $firstItem->getSummary());
    }

    public function testSetPagingPropertiesWillForceReload()
    {
        $firstApiCall   = new SentMessagesPage(0, 1);
        $secondApiCall  = [$this->newMessage('This is a message')];

        $this->esendexApi
            ->expects($this->exactly(2))
            ->method('getSentMessages')
            ->will($this->onConsecutiveCalls($firstApiCall, $secondApiCall));

        $this->collection->loadData();

        $this->assertTrue($this->collection->isLoaded());

        $this->collection->setPageSize(100);
        $this->collection->setCurPage(5);

        $this->assertFalse($this->collection->isLoaded());
    }

    public function testCollectionRethrowsHttpException()
    {
        $this->esendexApi
            ->expects($this->once())
            ->method('getSentMessages')
            ->will($this->throwException(new EsendexException()));

        $this->setExpectedException('Esendex\Exceptions\EsendexException');

        $this->collection->loadData();
    }
}
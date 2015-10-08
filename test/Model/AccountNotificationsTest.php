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
use DateTime;
use Esendex\Model\Account;

/**
 * Class AccountNotificationsTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class AccountNotificationsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Esendex_Sms_Model_AccountNotifications
     */
    protected $accountNotifications;

    protected $helper;

    public function setUp()
    {
        $helper = $this->getMockBuilder('Mage_Core_Helper_Abstract')
            ->setMethods(array('__'))
            ->getMock();

        $helper
            ->expects($this->any())
            ->method('__')
            ->will($this->returnCallback(function() {
                $args = func_get_args();
                return vsprintf(array_shift($args), $args);
            }));

        $this->accountNotifications = $this->getMockBuilder('Esendex_Sms_Model_AccountNotifications')
            ->setMethods(array('helper'))
            ->getMock();

        $this->accountNotifications
            ->expects($this->any())
            ->method('helper')
            ->will($this->returnValue($helper));
    }

    /**
     * @dataProvider accountExpiryProvider
     * @param DateTime $expires
     * @param bool $expected
     */
    public function testAccountExpiredOrLooming(DateTime $expires, $expected)
    {
        $this->accountNotifications->setCurrentDate('21 December 2014');

        $account = new Account;
        $account->expiresOn($expires);

        $this->assertEquals($expected, $this->accountNotifications->accountExpiryLooming($account));
    }

    /**
     * @return array
     */
    public function accountExpiryProvider()
    {
        return array(
            array(new DateTime('18 December 2014'), true),
            array(new DateTime('7 December 2014'), true),
            array(new DateTime('5 December 2014'), true),
            array(new DateTime('28 December 2014'), true),
            array(new DateTime('4 January 2015 00:01'), false),
            array(new DateTime('4 January 2015 00:00'), true),
            array(new DateTime('5 January 2015'), false),
        );
    }

    /**
     * @dataProvider remainingMessagesProvider
     * @param int $remaining
     * @param bool $expected
     */
    public function testRemainingMessagesBelowThreshold($remaining, $expected)
    {
        $accountNotifications = $this->getMockBuilder('Esendex_Sms_Model_AccountNotifications')
            ->setMethods(array('getWarnLimit'))
            ->getMock();

        $accountNotifications
            ->expects($this->once())
            ->method('getWarnLimit')
            ->will($this->returnValue(20));

        $account = new Account;
        $account->messagesRemaining($remaining);
        $this->assertEquals($expected, $accountNotifications->remainingMessagesBelowThreshold($account));
    }

    /**
     * @return array
     */
    public function remainingMessagesProvider()
    {
        return array(
            array(10, true),
            array(20, true),
            array(21, false),
            array(0, true),
            array(100, false),
        );
    }


    public function testGetExpiryNotificationReturnsCorrectMessageWhenExpired()
    {
        $this->accountNotifications->setCurrentDate('21 December 2014');
        $notification = $this->accountNotifications->getAccountExpiredOrIncorrectDetailsNotification('EX1000');

        $expected  = '<strong>Your Esendex account EX1000 has expired or your account details are incorrect. </strong>';
        $expected .= 'To continue sending SMS <a href="https://www.esendex.com/redirect?i=ecommerce&ls=magento&sc=trialexpiredbanner&sd=v1" target="_blank">buy messages</a> or ';
        $expected .= 'contact us at <a href="mailto:support@esendex.com">support@esendex.com.</a>';

        $this->assertEquals($expected, $notification);
    }

    public function testGetExpiryNotificationReturnsCorrectMessageWhenNearingExpiry()
    {
        $this->accountNotifications->setCurrentDate('21 December 2014');

        $account = new Account;
        $account->expiresOn(new DateTime('30 December 2014'));
        $account->reference('EX123');

        $notification = $this->accountNotifications->getExpiryNotification($account);

        $expected  = '<strong>You have 9 days left on your Esendex account EX123. </strong>';
        $expected .= '<a href="https://www.esendex.com/redirect?i=ecommerce&amp;ls=magento&amp;sc=trialexpirybanner&amp;sd=v1" target="_blank">Buy messages</a> to extend your account.';

        $this->assertEquals($expected, $notification);
    }
}

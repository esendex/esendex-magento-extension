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

use Esendex\Http\HttpException;
use Esendex\Model\Account;

/**
 * Class Esendex_Sms_Model_AccountObserver
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_AccountObserver
{
    /**
     * Validate whether the account details are valid/active
     *
     * @param Varien_Event_Observer $observer
     */
    public function validateAccount(Varien_Event_Observer $observer)
    {
        $account = null;
        try {
            $account = Esendex_Sms_Model_Api_Factory::getAccount(false);
        } catch (HttpException $e) {
        }

        if ($account instanceof Account) {
            Mage::getSingleton('core/session')->addSuccess(
                sprintf('Esendex Account %s details are correct', $account->reference())
            );
        } else {
            Mage::getSingleton('core/session')->addError('Account details are invalid or account has expired');
        }

        Mage::dispatchEvent('esendex_account_details_updated', array('account' => $account));
    }

    /**
     * Save the Account in the Cache
     *
     * @param Varien_Event_Observer $observer
     */
    public function rebuildAccountCache(Varien_Event_Observer $observer)
    {
        $account = $observer->getData('account');

        if (!$account instanceof Account) {
            Mage::app()->getCache()->remove('esendex_account');
            return;
        }
        Mage::app()->getCache()->save(serialize($account), 'esendex_account');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function refreshAccountCache(Varien_Event_Observer $observer)
    {
        if ($this->helper()->hasNotFilledInAccountDetails()) {
            return false;
        }

        //event listener will trigger re-cache
        try {
            $account = Esendex_Sms_Model_Api_Factory::getAccount(false);
        } catch (HttpException $e) {
        }
    }

    /**
     * If an Esendex Account is cached, update the remaining messages to reflect what we just sent
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateRemainingMessages(Varien_Event_Observer $observer)
    {
        $messagesSent = $observer->getData('message_count');

        if ($account = Mage::app()->getCache()->load('esendex_account')) {
            /** @var Account $account */
            $account = unserialize($account);
            $account->messagesRemaining($account->messagesRemaining() - $messagesSent);

            //re-save account to cache
            Mage::app()->getCache()->save(serialize($account), 'esendex_account');
        }
    }

    /**
     * @return Esendex_Sms_Helper_Data
     */
    public function helper()
    {
        return Mage::helper('esendex_sms');
    }
}

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

use Esendex\Model\Account;

/**
 * Class Esendex_Sms_Model_AccountObserver
 * @author Michael Woodward <michael@wearejh.com>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_AccountNotifications
{
    /**
     * Buy messages URL when expiring
     */
    const TRIAL_EXPIRING_URL =
        'https://www.esendex.com/redirect?i=ecommerce&amp;ls=magento&amp;sc=trialexpirybanner&amp;sd=v1';

    /**
     * Buy messages URL when expired
     */
    const TRIAL_EXPIRED_URL =
        'https://www.esendex.com/redirect?i=ecommerce&ls=magento&sc=trialexpiredbanner&sd=v1';

    /**
     * @var string
     */
    protected $accountExpiryWarningPeriod = 'P2W';  //2 weeks

    /**
     * Current date string for better unit tests
     * @var string
     */
    protected $currentDate = 'today 00:00:00';

    /**
     * @param Account $account
     * @return bool
     */
    public function accountExpiryLooming(Account $account)
    {
        $now = new DateTime($this->currentDate);

        $expiryWarnDate = clone $account->expiresOn();
        $expiryWarnDate->sub(new DateInterval($this->accountExpiryWarningPeriod));

        return $now >= $expiryWarnDate;
    }

    /**
     * @param Account $account
     *
     * @return bool
     */
    public function remainingMessagesBelowThreshold(Account $account)
    {
        return $account->messagesRemaining() <= $this->getWarnLimit();
    }

    /**
     * @param Account $account
     * @return string
     */
    public function getExpiryNotification(Account $account)
    {
        $now    = new DateTime($this->currentDate);
        $diff   = $account->expiresOn()->diff($now);

        $message  = '<strong>You have %d days left on your Esendex account %s. </strong>';
        $message .= '<a href="%s" target="_blank">Buy messages</a> to extend your account.';

        return $this->helper()->__($message, $diff->days, $account->reference(), static::TRIAL_EXPIRING_URL);
    }

    /**
     * @param string $savedAccountReference
     * @return string
     */
    public function getAccountExpiredOrIncorrectDetailsNotification($savedAccountReference)
    {
        $message  = '<strong>Your Esendex account %s has expired or your account details are incorrect. </strong>';
        $message .= 'To continue sending SMS <a href="%s" target="_blank">buy messages</a> or ';
        $message .= 'contact us at <a href="mailto:support@esendex.com">support@esendex.com.</a>';

        return $this->helper()->__(
            $message,
            $savedAccountReference,
            static::TRIAL_EXPIRED_URL
        );
    }

    /**
     * @return string
     */
    public function getInvalidAuthNotification()
    {
        $message  = '<strong>Your Esendex account %s has expired or your account details are incorrect. </strong>';
        $message .= 'To continue sending SMS <a href="%s" target="_blank">buy messages</a> or ';
        $message .= 'contact us at <a href="mailto:support@esendex.com">support@esendex.com.</a>';

        return $this->helper()->__(
            $message,
            Mage::getStoreConfig('esendex_sms/sms/account_reference'),
            static::TRIAL_EXPIRING_URL
        );
    }

    /**
     * @return array|bool
     */
    public function getNotifications()
    {
        if ($this->helper()->hasNotFilledInAccountDetails()) {
            return array();
        }

        try {
            $account = Esendex_Sms_Model_Api_Factory::getAccount();
        } catch (\Esendex\Http\HttpException $e) {
            //account not authorised
            $notifications[] = $this->getInvalidAuthNotification();
            return $notifications;
        }

        if (null === $account) {
            return array($this->getAccountExpiredOrIncorrectDetailsNotification(
                Mage::getStoreConfig('esendex_sms/sms/account_reference')
            ));
        }

        $notifications = array();
        if ($this->accountExpiryLooming($account)) {
            $notifications[] = $this->getExpiryNotification($account);
        }

        if ($this->hasWarnLimit() && $this->remainingMessagesBelowThreshold($account)) {
            $notifications[] = $this->helper()->__(
                'Your Esendex account %1$s has %2$d messages left',
                $account->reference(),
                $account->messagesRemaining()
            );
        }

        return $notifications;
    }

    /**
     * If the warn limit is numerical we will assume that the warn
     * limit notification should be applied
     *
     * @return bool
     */
    protected function hasWarnLimit()
    {
        return is_numeric(Mage::getStoreConfig('esendex_sms/sms/warn_me'));
    }

    /**
     * Get the message limit in which the admin would like to be warned at
     *
     * @return int
     */
    protected function getWarnLimit()
    {
        return (int) Mage::getStoreConfig('esendex_sms/sms/warn_me');
    }

    /**
     * Set the current date for better unit tests
     *
     * @param string $date
     */
    public function setCurrentDate($date)
    {
        $this->currentDate = $date;
    }

    /**
     * @return Esendex_Sms_Helper_Data
     */
    public function helper()
    {
        return Mage::helper('esendex_sms');
    }
}

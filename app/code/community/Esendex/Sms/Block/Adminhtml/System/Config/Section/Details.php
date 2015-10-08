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
 * Class Esendex_Sms_Block_Adminhtml_System_Config_Section_Details
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Sms_Block_Adminhtml_System_Config_Section_Details
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * @var string
     */
    protected $_template = 'esendex/sms/system/config/section/details.phtml';

    /**
     * @var array
     */
    protected $account;

    /**
     * @return string
     */
    public function getBuyMessagesLink()
    {
        return sprintf(
            'https://www.esendex.com/redirect?i=ecommerce&ls=magento&sc=connectstore&sd=v1&returnUri=%s',
            Mage::getBaseUrl()
        );
    }

    /**
     * @return string
     */
    public function getSmsMarketingLink()
    {
        return 'https://www.esendex.com/redirect?i=bulksend&ls=magento&sc=connectstore&sd=v1';
    }

    /**
     * @return string
     */
    public function getAutoRespondersLink()
    {
        return 'https://www.esendex.com/redirect?i=receivesettings&ls=magento&sc=connectstore&sd=v1';
    }

    /**
     * @return string
     */
    public function getSentMessagesLink()
    {
        return 'https://www.esendex.com/redirect?i=sentmessages&ls=magento&sc=connectstore&sd=v1';
    }

    /**
     * @return string
     */
    public function getSupportLink()
    {
        return 'http://support.esendex.co.uk/magento?i=magentosupport&ls=magento&sc=settings&sd=v1';
    }

    public function getSignUpUrl()
    {
        return 'http://www.esendex.com/redirect?i=magentosignup&ls=magento&sc=connectstore&sd=v1';
    }

    /**
     * Checks if the account details have not been filled in
     *
     * @return bool
     */
    public function accountDetailsNotFilledIn()
    {
        //assume not filled in if acc ref not filled in
        $accRef = Mage::getStoreConfig('esendex_sms/sms/account_reference');
        return  $accRef === '' || $accRef === null;
    }

    /**
     * @return bool
     */
    public function hasAccountError()
    {
        return !$this->getAccount() instanceof Account;
    }

    /**
     * Get Account Type
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->getAccount()->type();
    }

    /**
     * Get Remaining Messages
     *
     * @return int
     */
    public function getRemainingMessages()
    {
        return $this->getAccount()->messagesRemaining() ?: 0;
    }

    /**
     * @return string
     */
    public function getAccountReference()
    {
        return $this->getAccount()->reference();
    }

    /**
     * Get Expiration Date
     *
     * @return string
     */
    public function getAccountExpirationDate()
    {
        return $this->getAccount()->expiresOn();
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    /**
     * If there was an error getting the account
     * return false
     *
     * @return bool|Account
     */
    public function getAccount()
    {
        if (!$this->account) {
            try {
                $this->account = Esendex_Sms_Model_Api_Factory::getAccount();
            } catch (\Esendex\Http\HttpException $e) {
                $this->account = false;
            }
        }
        return $this->account;
    }
}
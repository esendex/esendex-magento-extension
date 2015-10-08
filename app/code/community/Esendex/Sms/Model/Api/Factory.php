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

use Esendex\DispatchService;
use Esendex\SentMessagesService;
use Esendex\Exceptions\EsendexException;
use Esendex\Authentication\LoginAuthentication;

/**
 * Class Esendex_Sms_Model_Api_Api
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Api_Factory
{

    /**
     * @return Esendex_Sms_Model_Api_Api
     */
    public static function getInstance()
    {
        $authentication         = static::getLoginAuth();
        $dispatchService        = new \Esendex\DispatchService($authentication, static::getHttpClient());
        $sentMessagesService    = new SentMessagesService($authentication);

        $config = [
            'debugMode'      => (bool) Mage::getStoreConfig('esendex_sms/sms/debug_mode'),
            'performSend'    => (bool) Mage::getStoreConfig('esendex_sms/sms/send_sms'),
        ];

        $logger = Esendex_Sms_Model_Logger_Factory::getInstance();

        return new Esendex_Sms_Model_Api_Api($logger, $dispatchService, $sentMessagesService, $config);
    }

    /**
     * @param bool $loadCached
     * @return \Esendex\Model\Account
     */
    public static function getAccount($loadCached = true)
    {
        if ($loadCached) {
            $cache = Mage::app()->getCache();
            if ($account = $cache->load('esendex_account')) {
                return unserialize($account);
            }
        }

        $authentication = static::getLoginAuth();
        $accountService = new Esendex\AccountService($authentication, static::getHttpClient());
        $account = $accountService->getAccount();

        Mage::dispatchEvent('esendex_account_reloaded', array('account' => $account));
        return $account;
    }

    /**
     * @return LoginAuthentication
     */
    public static function getLoginAuth()
    {
        return new LoginAuthentication(
            Mage::getStoreConfig('esendex_sms/sms/account_reference'),
            Mage::getStoreConfig('esendex_sms/sms/email'),
            Mage::getStoreConfig('esendex_sms/sms/password')
        );
    }

    /**
     * @return Esendex_Sms_Model_Http_HttpClient
     */
    public static function getHttpClient()
    {
        $apiCertLocations = array(
            sprintf('%s/lib/ca-bundle.pem', Mage::getBaseDir()),
            sprintf('%s/../vendor/esendex/sdk/src/ca-bundle.pem', Mage::getBaseDir()),
        );

        $availableLocations = array_filter(
            $apiCertLocations,
            function ($location) {
                return file_exists($location);
            }
        );

        if (!count($availableLocations)) {
            throw new RuntimeException(
                sprintf(
                    'Cannot locate Certificate file in either of: "%s"',
                    implode('", "', $apiCertLocations)
                )
            );
        }

        $apiCertLocation    = current($availableLocations);
        $userAgent          = static::getUserAgent();
        return new Esendex_Sms_Model_Http_HttpClient($userAgent, $apiCertLocation, true);
    }

    /**
     * @return string
     */
    public static function getUserAgent()
    {
        return sprintf(
            'esendex-magento-plugin/%s magento/%s (%s)',
            Mage::getConfig()->getModuleConfig("Esendex_Sms")->version,
            Mage::getVersion(),
            php_uname()
        );
    }
}

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

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 * Class Esendex_Sms_Model_Logger
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Logger_Factory
{
    /**
     * Esendex Log File
     */
    const LOG_FILE = 'esendex_sms.log';

    /**
     * @var LoggerInterface
     */
    static $logger;

    /**
     * @return Esendex_Sms_Model_Logger_Logger
     */
    public static function getInstance()
    {
        if (static::$logger) {
            return static::$logger;
        }

        $file   = sprintf('%s/%s', Mage::getBaseDir('log'), static::LOG_FILE);
        $logger = new Zend_Log(new Zend_Log_Writer_Stream($file));

        if (!Mage::getStoreConfig('esendex_sms/sms/debug_mode')) {
            $logger->addFilter(new Zend_Log_Filter_Priority(Zend_Log::CRIT));
        }

        static::$logger = new Esendex_Sms_Model_Logger_Logger($logger);
        return static::$logger;
    }
}

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

/**
 * Class Esendex_Sms_Model_Logger
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Logger_Logger extends AbstractLogger
{

    /**
     * Map PSR Log Level to Zend Log Level
     *
     * @var array
     */
    private $logMap = [
        LogLevel::ALERT     => Zend_Log::ALERT,
        LogLevel::CRITICAL  => Zend_Log::CRIT,
        LogLevel::DEBUG     => Zend_Log::DEBUG,
        LogLevel::EMERGENCY => Zend_Log::EMERG,
        LogLevel::ERROR     => Zend_Log::ERR,
        LogLevel::INFO      => Zend_Log::INFO,
        LogLevel::NOTICE    => Zend_Log::NOTICE,
        LogLevel::WARNING   => Zend_Log::WARN,
    ];

    /**
     * @var Zend_Log
     */
    private $logger;

    /**
     * @param Zend_Log $logger
     */
    public function __construct(Zend_Log $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param int    $level
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        if (!isset($this->logMap[$level])) {
            throw new \InvalidArgumentException('Level is not supported. See "Psr\Log\LogLevel"');
        }

        $zendLogLevel = $this->logMap[$level];

        //Proxy log
        $this->logger->log($message, $zendLogLevel);
    }
}

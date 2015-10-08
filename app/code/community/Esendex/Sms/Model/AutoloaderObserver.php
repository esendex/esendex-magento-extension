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
 * Class Esendex_Sms_Model_AutoloaderObserver
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_AutoloaderObserver
{
    /**
     * Prefixes of classes to autoload
     *
     * @var array
     */
    protected static $prefixes = array(
        'Esendex',
        'Psr'
    );

    /**
     * Whether or not the autoloader has been registered
     *
     * @var bool
     */
    protected static $autoloaderRegistered = false;

    /**
     * Register Esendex PSR0 Autoloader, before Varien_Autoload
     * so we don't get and warnings about varien_Autoload not being able
     * to find the class.
     */
    public function autoloadEsendexSdk()
    {
        if (!static::$autoloaderRegistered) {

            $this->checkLibExistence();

            //find and unregister Varien Autoloader
            foreach (spl_autoload_functions() as $autoLoader) {
                if (is_array($autoLoader) && $autoLoader[0] instanceof Varien_Autoload) {
                    $this->register($autoLoader);
                    break;
                }
            }

            static::$autoloaderRegistered = true;
        }
    }

    /**
     * @param callable $autoLoader
     */
    public function register($autoLoader)
    {
        spl_autoload_unregister($autoLoader);
        //register Esendex Autoloader
        spl_autoload_register([$this, 'load']);
        //re-register Magento Autoloader
        spl_autoload_register($autoLoader);
    }

    /**
     * Check if Esendex & Psr/Log Library exists in the Magento lib.
     *
     * @throws Mage_Core_Exception
     */
    public function checkLibExistence()
    {
        $esendexLibDir = sprintf('%s/Esendex', Mage::getBaseDir('lib'));
        if (!file_exists($esendexLibDir)) {
            Mage::throwException(
                sprintf(
                    'Esendex SDK not found in: "%s". If you are using the module via Composer ' .
                    'you should stop this observer from triggering',
                    $esendexLibDir
                )
            );
        }

        $psrLogLibDir = sprintf('%s/Psr/Log', Mage::getBaseDir('lib'));
        if (!file_exists($psrLogLibDir)) {
            Mage::throwException(
                sprintf(
                    'Psr/Log library not found in: "%s". If you are using the module via Composer ' .
                    'you should stop this observer from triggering',
                    $psrLogLibDir
                )
            );
        }}

    /**
     * @param string $class
     */
    public function load($class)
    {
        if (!$this->shouldClassBeAutoLoaded(static::$prefixes, $class)) {
            return;
        }

        $parts  = explode('\\', $class);
        $path   = sprintf("%s/%s.php", Mage::getBaseDir('lib'), implode('/', $parts));

        if (file_exists($path)) {
            require_once($path);
        }
    }

    /**
     * If this class name matches any of our defined prefixes, return true
     *
     * @param array $prefixes
     * @param string $className
     * @return int
     */
    private function shouldClassBeAutoLoaded(array $prefixes, $className)
    {
        return count(array_filter($prefixes, function ($prefix) use ($className) {
            return $prefix === substr($className, 0, strlen($prefix));
        }));
    }
}
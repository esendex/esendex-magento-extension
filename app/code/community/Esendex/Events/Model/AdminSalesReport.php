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
 * Class Esendex_Sms_Model_Trigger_AdminSalesReport
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Events_Model_AdminSalesReport extends Esendex_Sms_Model_TriggerAbstract
{

    const ENTITY    = 'esendex_sms_trigger_admin_sales_report';
    const CACHE_TAG = 'esendex_sms_trigger_admin_sales_report';

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('esendex_events/adminSalesReport');
    }

    /**
     * Filter & validate recipients
     */
    public function validate()
    {
        parent::validate();

        $recipients = explode("\n", str_replace("\r", '', $this->getData('recipients')));
        $recipients = array_unique(array_map('trim', $recipients));
        $recipients = array_filter($recipients, 'strlen');

        if (false === $recipients || count($recipients) < 1) {
            $this->addError('Recipients cannot be empty');
        }

        try {
            $startDate = new DateTime($this->getData('start_date'));
        } catch (Exception $e) {
            $this->addError('Invalid Start Date');
            return !$this->hasErrors();
        }

        // Get last errors & warnings
        $errors = DateTime::getLastErrors();

        if (!empty($errors['errors']) || !empty($errors['warnings'])) {
            $this->addError('Invalid Start Date');
        }

        // Set filtered & validated state
        $this->setData('recipients', $recipients);
        $this->setData('start_date', $startDate->format(Varien_Date::DATETIME_PHP_FORMAT));

        return !$this->hasErrors();
    }
}

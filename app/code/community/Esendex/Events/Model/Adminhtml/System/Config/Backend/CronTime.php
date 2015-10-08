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
 * Class Esendex_Events_Model_Adminhtml_System_Config_Backend_CronTime
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Events_Model_Adminhtml_System_Config_Backend_CronTime extends Mage_Core_Model_Config_Data
{
    /**
     * @var string
     */
    protected $cronCode = 'esendex_sales_report';

    /**
     * Save the time so it can be accessed by the scheduler
     */
    protected function _afterSave()
    {
        $cronPath       = sprintf('crontab/jobs/%s/schedule/cron_expr', $this->cronCode);
        $time           = $this->getData('groups/mobile_sales_report/fields/time/value');

        $cronExprArray  = array(intval($time[1]), intval($time[0]), '*', '*', '*');
        $cronExprString = implode(' ', $cronExprArray);

        Mage::getModel('core/config_data')
            ->load($cronPath, 'path')
            ->setValue($cronExprString)
            ->setPath($cronPath)
            ->save();
    }
}
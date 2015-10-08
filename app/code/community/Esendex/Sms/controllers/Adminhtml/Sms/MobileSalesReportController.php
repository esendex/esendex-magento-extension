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

require_once __DIR__ . '/TriggerController.php';

/**
 * Class Esendex_Sms_Adminhtml_Sms_MobileSalesReportController
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Adminhtml_Sms_MobileSalesReportController extends Esendex_Sms_Adminhtml_Sms_TriggerController
{

    /**
     * The messages and titles used throught this class
     *
     * @var array
     */
    protected $messages = array(
        'index'                 => 'Manage Mobile Sales Reports',
        'not-exist'             => 'This Mobile Sales Report no longer exists',
        'edit'                  => 'Edit Mobile Sales Report',
        'new'                   => 'Add Mobile Sales Report',
        'save-success'          => 'Mobile Sales Report was successfully saved',
        'save-error'            => 'There was a problem saving the Mobile Sales Report',
        'delete-success'        => 'Mobile Sales Report was successfully deleted',
        'delete-error'          => 'There was an error deleting Mobile Sales Report',
        'mass-delete-invalid'   => 'Please select Admin Sales Reports to delete',
        'mass-delete-error'     => 'There was an error deleting Admin Sales Reports',
        'mass-delete-success'   => 'Total of %d Admin Sales Reports were successfully deleted',
        'mass-status-invalid'   => 'Please select Admin Sales Reports',
        'mass-status-error'     => 'There was an error updating Admin Sales Reports',
        'mass-status-success'   => 'Total of %d Admin Sales Reports were successfully updated',
    );

    /**
     * File prefix
     *
     * @var string
     */
    protected $filePrefix = 'admin_sales_reports';
}

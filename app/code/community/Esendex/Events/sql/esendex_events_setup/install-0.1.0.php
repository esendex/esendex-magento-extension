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

/** @var $this Esendex_Sms_Model_Resource_Setup */
$this->startSetup();


$this->addEvent(
    'Order Shipped with Tracking',
    'event',
    'sales_order_shipment_save_after',
    0,
    'esendex_events/eventProcessor_orderShippedWithTracking'
);

$this->addEvent(
    'Order Shipped',
    'event',
    'sales_order_shipment_save_after',
    1,
    'esendex_events/eventProcessor_orderShipped'
);

$this->addEvent(
    'Order Status Changed - Canceled',
    'event',
    'sales_order_save_after',
    2,
    'esendex_events/eventProcessor_orderStatusChange_canceled'
);

$this->addEvent(
    'Order Status Changed - Closed',
    'event',
    'sales_order_save_after',
    3,
    'esendex_events/eventProcessor_orderStatusChange_closed'
);

$this->addEvent(
    'Order Status Changed - Complete',
    'event',
    'sales_order_save_after',
    4,
    'esendex_events/eventProcessor_orderStatusChange_complete'
);

$this->addEvent(
    'Order Status Changed - On Hold',
    'event',
    'sales_order_save_after',
    5,
    'esendex_events/eventProcessor_orderStatusChange_onHold'
);

$this->addEvent(
    'Order Status Changed - Payment Review',
    'event',
    'sales_order_save_after',
    6,
    'esendex_events/eventProcessor_orderStatusChange_paymentReview'
);

$this->addEvent(
    'Order Status Changed - Pending',
    'event',
    'sales_order_save_after',
    7,
    'esendex_events/eventProcessor_orderStatusChange_pending'
);

$this->addEvent(
    'Order Status Changed - Pending Payment',
    'event',
    'sales_order_save_after',
    8,
    'esendex_events/eventProcessor_orderStatusChange_pendingPayment'
);

$this->addEvent(
    'Order Status Changed - Processing',
    'event',
    'sales_order_save_after',
    10,
    'esendex_events/eventProcessor_orderStatusChange_processing'
);

$this->addEvent(
    'Order Status Changed - Suspected Fraud',
    'event',
    'sales_order_save_after',
    11,
    'esendex_events/eventProcessor_orderStatusChange_suspectedFraud'
);

$this->addEvent(
    'Admin Sales Report',
    'cron',
    'esendex_sales_report',
    12,
    'esendex_events/eventProcessor_adminSalesReport',
    'esendex_events/adminSalesReport'
);

// Create New Table for Extra Admin Sales Report Recipients Data
$tableName = $this->getTable('esendex_events/admin_sales_report_recipients');
if (!$this->getConnection()->isTableExists($tableName)) {
    $table = $this->getConnection()
        ->newTable($this->getTable($tableName))
        ->addColumn(
            'trigger_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'nullable' => false,
            ),
            'Trigger Id'
        )
        ->addColumn(
            'recipient',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            64,
            array(
                'nullable' => false,
            ),
            'Recipient Number'
        )
        ->addForeignKey(
            $this->getFkName('esendex_events/admin_sales_report_recipients', 'trigger_id', 'esendex_sms/trigger', 'entity_id'),
            'trigger_id',
            $this->getTable('esendex_sms/trigger'),
            'entity_id'
        )
        ->setComment('Trigger to Admin Sales Recipient Table');

    $this->getConnection()->createTable($table);
}

// Create New Table for Extra Admin Sales Report Recipients Data
$tableName = $this->getTable('esendex_events/admin_sales_report_details');
if (!$this->getConnection()->isTableExists($tableName)) {
    $table = $this->getConnection()
        ->newTable($this->getTable($tableName))
        ->addColumn(
            'trigger_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'nullable' => false,
            ),
            'Trigger Id'
        )
        ->addColumn(
            'frequency',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            64,
            array(
                'nullable' => false,
            ),
            'Report Frequency'
        )
        ->addColumn(
            'start_date',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(
                'nullable' => false,
            ),
            'Report Start Date'
        )
        ->addForeignKey(
            $this->getFkName('esendex_events/admin_sales_report_details', 'trigger_id', 'esendex_sms/trigger', 'entity_id'),
            'trigger_id',
            $this->getTable('esendex_sms/trigger'),
            'entity_id'
        )
        ->setComment('Trigger to Admin Sales Detail Table');

    $this->getConnection()->createTable($table);
}

$this->endSetup();

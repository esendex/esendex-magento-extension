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

$table = $this->getConnection()
    ->newTable($this->getTable('esendex_sms/event'))
    ->addColumn(
        'entity_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Event ID'
    )
    ->addColumn(
        'save_model',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(),
        'This maps to the model that saves additional data'
    )
    ->addColumn(
        'event_processor',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        255,
        array(),
        'This maps to the event processor model that handles the event'
    )
    ->addColumn(
        'name',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        75,
        array(
            'nullable'  => false,
        ),
        'Event Friendly Name'
    )
    ->addColumn(
        'message_template',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        1024,
        array(
            'nullable'  => false,
        ),
        'Message Template'
    )
    ->addColumn(
        'trigger_type',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        null,
        array(
            'nullable'  => false,
        ),
        'The trigger type, could be a Magento Event or Magento Cron'
    )
    ->addColumn(
        'trigger_code',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        150,
        array(
            'nullable'  => false,
        ),
        'Magento Event or Magento Cron this event maps to'
    )
    ->addColumn(
        'order',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullanle' => false
        ),
        'Order'
    )
    ->addIndex(
        $this->getIdxName('esendex_sms/event', array('name'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        "name" ,
        array("type" => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addIndex($this->getIdxName('esendex_sms/event', array('trigger_code')), 'trigger_code')
    ->setComment('Event Table');

$this->getConnection()->createTable($table);

$table = $this->getConnection()
    ->newTable($this->getTable('esendex_sms/trigger'))
    ->addColumn(
        'entity_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Trigger ID'
    )
    ->addColumn(
        'event_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable'  => false,
        ),
        'Esendex Event ID'
    )
    ->addForeignKey(
        $this->getFkName('esendex_sms/trigger', 'event_id', 'esendex_sms/event', 'entity_id'),
        'event_id',
        $this->getTable('esendex_sms/event'),
        'entity_id'
    )
    ->addColumn(
        'sender',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        150,
        array(
            'nullable'  => false,
        ),
        'Sender'
    )
    ->addColumn(
        'description',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        null,
        array(
            'nullable'  => false,
        ),
        'Trigger description'
    )
    ->addColumn(
        'message_template',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        null,
        array(
            'nullable'  => false,
        ),
        'Message Template'
    )
    ->addColumn(
        'status',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(),
        'Enabled'
    )
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(),
        'Trigger Modification Time'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(),
        'Trigger Creation Time'
    )
    ->setComment('Table');

$this->getConnection()->createTable($table);

$table = $this->getConnection()
    ->newTable($this->getTable('esendex_sms/trigger_store'))
    ->addColumn('trigger_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'primary'   => true,
        ), 'Trigger ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store ID')
    ->addIndex($this->getIdxName('esendex_sms/trigger_store', array('store_id')), array('store_id'))
    ->addForeignKey($this->getFkName('esendex_sms/trigger_store', 'trigger_id', 'esendex_sms/trigger', 'entity_id'), 'trigger_id', $this->getTable('esendex_sms/trigger'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($this->getFkName('esendex_sms/trigger_store', 'store_id', 'core/store', 'store_id'), 'store_id', $this->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Triggers To Store Linkage Table');

$this->getConnection()->createTable($table);

// Create New Table for Event Sample Message Template
$table = $this->getConnection()
    ->newTable($this->getTable('esendex_sms/event_sample_message_template'))
    ->addColumn(
        'entity_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'ID'
    )
    ->addColumn(
        'event_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'nullable'  => false,
        ),
        'Event Id'
    )
    ->addColumn(
        'locale_code',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        10,
        array(
            'nullable'  => false,
        ),
        'Locale Code'
    )
    ->addColumn(
        'message_template',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        1024,
        array(
            'nullable'  => false,
        ),
        'Sample Message Template'
    )
    ->addForeignKey(
        $this->getFkName('esendex_sms/event_sample_message_template', 'event_id', 'esendex_sms/event', 'entity_id'),
        'event_id',
        $this->getTable('esendex_sms/event'),
        'entity_id'
    )
    ->addIndex(
        'event_id_locale_code_unique',
        ['event_id', 'locale_code'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE]
    )
    ->setComment('Event Sample Message Template Table');

$this->getConnection()->createTable($table);

$this->endSetup();

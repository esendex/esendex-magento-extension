
Adding Fields To Your Notification
----------------------------------

Sometimes your notification may require more information and configuration. For this reason you may want to add extra fields to the `Add New Notification` form for your event.

When constructing the form a event is dispatched, it is a standard prefix followed by a normalised version of the event name. For example if your event name is `Customer Register Success` the event `esendex_sms_edit_form_stage2_customer_register_success` will be dispatched.
 You can hook in to this event and add extra fields to the form. 

We will now run through the process of adding extra fields and mapping the data to a database table. We will then 
show how to utilise this data in your `Event Processor`. The first step is to hook in to the event. We will continue
from the example module created in [Building A Custom Module](Building A Custom Module.md).

```xml
<!-- app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/etc/config.xml -->
<config>
    <global>
        <events>
            <esendex_sms_edit_form_stage2_customer_register_success>
                <observers>
                    <customer_register_success_add_fields>
                        <class>EsendexCustomNotifications_RegisterSuccessNotification_Model_Observer</class>
                        <method>addFields</method>
                    </customer_register_success_add_fields>
                </observers>
            </esendex_sms_edit_form_stage2_customer_register_success>
        </events>
    </global>
</config>
```

Imagine if we wanted to do something silly like only send SMS messages to people who register when their first name is the same as one
we specify when creating the Notification. We could add a text field to record this value.

```php
<?php
//app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/Model/Observer.php

class EsendexCustomNotifications_RegisterSuccessNotification_Model_Observer
{
    /**
     * @param Varien_Event_Observer $e
     */
    public function addFields(Varien_Event_Observer $e)
    {
        $fieldset = $e->getFieldset();
        $fieldset->addField('first_name', 'text', [
            'label'              => Mage::helper('esendex_sms')->__('First Name'),
            'name'               => 'first_name',
            'required'           => true,
        ]);
    }
}
```

This is all well and good, our field now shows on the `Add New Notification` form for the `Customer Register Success` Event, 
but if we save the notification with a value in the `first_name` field, it will be lost forever. 

This is because the table notifications are stored in, does not have a `first_name` column. In order 
to have your notification hold extra data, you will need to provide your own Resource Model and setup scripts to 
create the table where you will store this data. 

The setup script will look like this:

```php
<?php
//app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/sql/esendexcustomnotifications_setup/upgrade-0.1.0-0.2.0.php

/** @var $this Esendex_Sms_Model_Resource_Setup */
$this->startSetup();

// Create New Table for Extra Customer Register Success Data
$tableName = $this->getTable('esendexCustomNotifications_registerSuccessNotification/customer_register_details');
if (!$this->getConnection()->isTableExists($tableName)) {
    $table = $this->getConnection()
        ->newTable($this->getTable($tableName))
        ->addColumn(
            'trigger_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false,
            ],
            'Trigger Id'
        )
        ->addColumn(
            'first_name',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            255,
            [
                'nullable' => false,
            ],
            'First Name'
        )
        ->addForeignKey(
            $this->getFkName('esendexCustomNotifications_registerSuccessNotification/customer_register_details', 'trigger_id', 'esendex_sms/trigger', 'entity_id'),
            'trigger_id',
            $this->getTable('esendex_sms/trigger'),
            'entity_id'
        )
        ->setComment('Trigger to Admin Sales Detail Table');

    $this->getConnection()->createTable($table);
}

$this->endSetup();

```

We then need to alter our `config.xml` to inform Magento about our Resource models and assign tables to them. We also bump the module version `0.2.0` while we are here so that our setup script will run.

```xml
<!-- app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/etc/config.xml -->
<?xml version="1.0"?>
<config>
    <modules>
        <EsendexCustomNotifications_RegisterSuccessNotification>
            <!-- UPDATED -->
            <version>0.2.0</version>
            <!-- UPDATED -->
        </EsendexCustomNotifications_RegisterSuccessNotification>
    </modules>
    <global>
        <helpers>
            <esendexCustomNotifications_registerSuccessNotification>
                <class>EsendexCustomNotifications_RegisterSuccessNotification_Helper</class>
            </esendexCustomNotifications_registerSuccessNotification>
        </helpers>
        <models>
            <esendexCustomNotifications_registerSuccessNotification>
                <class>EsendexCustomNotifications_RegisterSuccessNotification_Model</class>
                <!-- NEW -->
                <resourceModel>esendexCustomNotifications_registerSuccessNotification_resource</resourceModel>
                <!-- NEW -->
            </esendexCustomNotifications_registerSuccessNotification>
            <!-- NEW -->
            <esendexCustomNotifications_registerSuccessNotification_resource>
                <class>EsendexCustomNotifications_RegisterSuccessNotification_Model_Resource</class>
                <entities>
                    <customer_register_details>
                        <table>customer_register_details</table>
                    </customer_register_details>
                </entities>
            </esendexCustomNotifications_registerSuccessNotification_resource>
            <!-- NEW -->
        </models>
        <resources>
            <esendexcustomnotifications_setup>
                <setup>
                    <module>EsendexCustomNotifications_RegisterSuccessNotification</module>
                    <class>Esendex_Sms_Model_Resource_Setup</class>
                </setup>
            </esendexcustomnotifications_setup>
        </resources>
        <events>
            <customer_register_success>
                <observers>
                    <customer_register>
                        <class>Esendex_Sms_Model_Observer</class>
                        <method>dispatchEvent</method>
                    </customer_register>
                </observers>
            </customer_register_success>

            <esendex_sms_edit_form_stage2_customer_register_success>
                <observers>
                    <customer_register_success_add_fields>
                        <class>EsendexCustomNotifications_RegisterSuccessNotification_Model_Observer</class>
                        <method>addFields</method>
                    </customer_register_success_add_fields>
                </observers>
            </esendex_sms_edit_form_stage2_customer_register_success>
        </events>
    </global>
</config>
```

Now we have created our database structure we need to create the models and resource models in order to validate and save the extra notification data to our tables.

### Create an Entity

The entity is used to validate the data. This is not necessary but should be encouraged. To validate, 
override the function with your own validation logic. Make sure to call the parent `validate()` method and also return 
whether there were actually any errors. The controller automatically calls this method and reports any errors to the front end.

Here you can see we just check that `first_name` is 4 characters or longer.

```php
<?php
//app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/Model/RegisterSuccess.php

class EsendexCustomNotifications_RegisterSuccessNotification_Model_RegisterSuccess
    extends Esendex_Sms_Model_TriggerAbstract
{

    const ENTITY    = 'esendex_sms_trigger_register_success';
    const CACHE_TAG = 'esendex_sms_trigger_register_success';

    /**
     * Constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('esendexCustomNotifications_registerSuccessNotification/registerSuccess');
    }

    /**
     * Filter & validate recipients
     */
    public function validate()
    {
        parent::validate();

        if (strlen($this->getData('first_name')) < 4) {
            $this->addError('first_name must be longer than 3 characters');
        }

        return !$this->hasErrors();
    }
}

```

### Create the resource model
The resource model is used to actually persist the data to the database. We need to extend the existing resource model 
in order to save `first_name` to our new database table. The resource class utilises the `_afterLoad`, `_afterSave` & 
`_beforeDelete` methods (which are automatically invoked for you) to manage the data in our extra table.

In this example whenever a notification of our type is loaded, the extra data from the other table is loaded and set on our entity. 
The `_afterSave` function persists our `first_name` variable to the `customer_register_details` table. But
before it does that  it deletes any existing record for the current notification (this is for when updating a notification). 
Finally, the `_beforeDelete` function takes care of removing the extra data from the `customer_register_details` table 
when the notification is removed.

```php
<?php
//app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/Model/Resource/RegisterSuccess.php

class EsendexCustomNotifications_RegisterSuccessNotification_Model_Resource_RegisterSuccess
    extends Esendex_Sms_Model_Resource_TriggerAbstract
{

    /**
     * Constructor
     */
    public function _construct()
    {
        $this->_init('esendex_sms/trigger', 'entity_id');
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return self
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $adapter = $this->_getReadAdapter();
            $select = $adapter->select()
                ->from($this->getTable('esendexCustomNotifications_registerSuccessNotification/customer_register_details'), array('first_name'))
                ->where('trigger_id = ?', (int) $object->getId());

            $firstName = $adapter->fetchOne($select);
            $object->setData('first_name', $firstName);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Assign trigger to store views
     *
     * @param Mage_Core_Model_Abstract $object
     * @return self
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $firstName  = $object->getData('first_name');
        $table      = $this->getTable('esendexCustomNotifications_registerSuccessNotification/customer_register_details');

        $where = [
            'trigger_id = ?' => (int) $object->getId(),
        ];
        $this->_getWriteAdapter()->delete($table, $where);

        $data = [
            'trigger_id'    => (int) $object->getId(),
            'first_name'    => $firstName
        ];
        $this->_getWriteAdapter()->insert($table, $data);
        return parent::_afterSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $table = $this->getTable('esendexCustomNotifications_registerSuccessNotification/customer_register_details');

        $where = [
            'trigger_id = ?' => (int) $object->getId(),
        ];
        $this->_getWriteAdapter()->delete($table, $where);

        return parent::_beforeDelete($object);
    }
}

```

### Create the resource collection
We also need to create a resource collection so when we load multiple notifications of our particular type (Customer Register Success)
the extra data in the other tables is loaded as well.

```php
<?php
//app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/Model/Resource/RegisterSuccess/Collection.php

class EsendexCustomNotifications_RegisterSuccessNotification_Model_Resource_RegisterSuccess_Collection
    extends Esendex_Sms_Model_Resource_Trigger_Collection
{

    /**
     * @var string ID Field Name
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Set Model Class
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('esendexCustomNotifications_registerSuccessNotification/registerSuccess');
    }

    /**
     * Join data from other tables
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $triggerIds = array_map(
            function (EsendexCustomNotifications_RegisterSuccessNotification_Model_RegisterSuccess $item) {
                return $item->getId();
            },
            $this->_items
        );

        $select = $this->getConnection()->select();
        $table = $this->getTable('esendexCustomNotifications_registerSuccessNotification/customer_register_details');
        $select->from($table, array('first_name', 'trigger_id'))
            ->where('trigger_id IN (?)', $triggerIds);

        $rows = $this->getConnection()->fetchAll($select);

        foreach ($rows as $row) {
            $triggerId  = $row['trigger_id'];
            $item       = $this->getItemByColumnValue($this->getIdFieldName(), $triggerId);
            $item->setData('first_name', $row['first_name']);
        }
    }
}

```

### Update the event

The last step we need to do is to update our Event in the database. When we created it initially, 
we created it without a `save_model`. If a `save_model` is not specified, a default one is used. In this case, 
we want to use the model we created in the previous steps. 
To do this we will delete and re-add the event, this involves creating another setup script.

```php
<?php
//app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/sql/esendexcustomnotifications_setup/upgrade-0.2.0-0.3.0.php

/** @var $this Esendex_Sms_Model_Resource_Setup */
$this->startSetup();

//remove old event
$this->deleteTableRow('esendex_sms/event', 'name', 'Customer Register Success');

//add again with custom save model
$this->addEvent(
    'Customer Register Success',
    'event',
    'customer_register_success',
    20,
    'esendexCustomNotifications_registerSuccessNotification/eventProcessor_customerRegisterSuccess',
    'esendexCustomNotifications_registerSuccessNotification/registerSuccess'
);

$this->endSetup();
```

We also need to bump the module version one last time to trigger the upgrade scripts


```xml
<!-- app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/etc/config.xml -->
<?xml version="1.0"?>
<config>
    ...
    <modules>
        <EsendexCustomNotifications_RegisterSuccessNotification>
            <version>0.3.0</version>
        </EsendexCustomNotifications_RegisterSuccessNotification>
    </modules>
    ...
</config>

```

If we now navigate to the admin panel and create a `Customer Register Success` notification we can specify the `first_name`
field. If we save and edit the record, we can see the data has been persisted. 

Our final step is to modify the `shouldSend` function to check the name of the registered customer against the allowed name
from our notification. The `shouldSend` function should be modified like so

```php
//app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/Model/EventProcessor/CustomerRegisterSuccess.php

class EsendexCustomNotifications_RegisterSuccessNotification_Model_EventProcessor_CustomerRegisterSuccess
    extends Esendex_Sms_Model_EventProcessor_Abstract
    implements Esendex_Sms_Model_EventProcessor_Interface
{
    ...
    
    /**
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     * @return bool
     */
    public function shouldSend(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        $addresses = $this->parameters
            ->getData('customer')
            ->getAddresses();

        if (!isset($addresses[0])) {
            return false;
        }

        $telephoneNumber = $addresses[0]->getTelephone();
        if ($telephoneNumber === null || $telephoneNumber === '') {
            return false;
        }
        
        //This extra check will only allow messages to be sent to customers
        //whose first name is the same as the one set when creating the notification
        $customerName = $this->parameters->getData('customer')->getFirstName();
        if ($customerName !== $trigger->getFirstName()) {
            return false;
        }

        return true;
    }
    
    ...
}
```

##Done!
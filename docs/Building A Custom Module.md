Building A Custom Module
--------------

The esendex modules are built in a way which allows for mutiple extension points. This allows for developers to build 
custom modules which can provide notifications which will be avaialble in the admin panel for store administrators to configure. 
This guide will detail how to build a custom module which provides a notification for the Esendex module, which will be avaialble in the admin panel.

## Customer Register SMS
We shall build a small module which provides a notification which store administrators can turn on. The notification 
will send a text message to the customer when they succesfully register an account on the store.


### Create a new module

![image](http://ss.jhf.tw/Mv4qJ8Kc1K.png)

Here we create a new module using `n98-magerun` ridiculously named `EsendexCustomNotifications_RegisterSuccessNotification`

We can now interact with the `Esendex_Sms` public API. The first step is to create a model which will provide the variable list and logic for determining whether to send. This model must implement the interface `Esendex_Sms_Model_EventProcessor_Interface`.


### Event Processor
This model is known internally as the `Event Processor` for your notification. Each Notification has it's own `Event Processor`.

We will implement the methods one by one. However, first we must create the class and implement the interface. 
It should also extend `Esendex_Sms_Model_EventProcessor_Abstract` which implements some of the interface methods for you. 
This is not a requirement, but it saves you some work.


Your folder structure should look like:

![image](http://ss.jhf.tw/QCYEOEZ6sV.png)

And your class:

```php
//app/code/community/EsendexCustomNotifications/RegisterSuccessNotification/Model/EventProcessor/CustomerRegisterSuccess.php

class EsendexCustomNotifications_RegisterSuccessNotification_Model_CustomerRegisterSuccess
    extends Esendex_Sms_Model_EventProcessor_Abstract
    implements Esendex_Sms_Model_EventProcessor_Interface
{
    public function getVariables()
    {}

    public function setParameters(Varien_Object $parameters)
    {}

    public function getVariableContainer(Esendex_Sms_Model_TriggerAbstract $trigger)
    {}

    public function shouldSend(Esendex_Sms_Model_TriggerAbstract $trigger)
    {}

    public function getRecipient(Esendex_Sms_Model_TriggerAbstract $trigger)
	{}

    public function postProcess($message)
    {}

    public function getStoreId()
    {}

}

```

#### getVariables()
This function should return an array of `Esendex_Sms_Model_Variable`. This function is implemented for you in `Esendex_Sms_Model_EventProcessor_Abstract` so you can remove this from your implementation.

#### setParameters()
This function is also implemeted for us in `Esendex_Sms_Model_EventProcessor_Abstract`. This function is used by the calling code to pass in any variables which are available in the Magento event. You will see later on that we will attach this `Event Processor` to a Magento Event. In particular the `customer_register_success` event. This event has data associated to it. It contains the customer object and the controller object.

This data is automatically injected in to our event processor using the `setParameters` method. This means we can access the `customer` object in the other methods which we need to implement. 

#### getVariableContainer()
Again, this function is implemented for us in `Esendex_Sms_Model_EventProcessor_Abstract`. However, this function may commonly be extended to provide extra variables for the notification. If we wanted a store name variable to be available to the notification, we may add it here. For example:

```php
public function getVariableContainer(Esendex_Sms_Model_TriggerAbstract $trigger)
{
    $this->parameters->setData(
        'store_name',
        Mage::getStoreConfig('general/store_information/name',
            $this->parameters->getData('customer')->getStoredId()
        )
    );
    return $this->parameters;
}

```

Here we are adding a `store_name` variable to the parameters object, where we get the value from the store config, using 
the store ID from the current user.

#### shouldSend(Esendex_Sms_Model_TriggerAbstract $trigger)
This function should always be implemented manually and will contain custom logic depending on what you're doing. In our example we only want to send a message if the customer actually filled in a telephone number.

Another example may be where you only send a text message based on the status of an order. You should write that logic in this message. 
If this method returns `false` then nothing else will happen with this event processor.

Therefore our code should check if an address is present and get the telephone from there:

```php
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

    return true;
}
```

#### getRecipient(Esendex_Sms_Model_TriggerAbstract $trigger)
If order for the Esendex module to be able to send your message, there must be a recipient. This cannot be guessed so you 
must tell the module the number. Where you get it from is your choice. It could be a store config setting, it could the current customers number. 
It can come from anywhere, in our case it will be from the customer.

```php
public function getRecipient(Esendex_Sms_Model_TriggerAbstract $trigger)
{
    $addresses = $this->parameters
        ->getData('customer')
        ->getAddresses();

    return $addresses[0]->getTelephone();
}

```

#### postProcess($message)
This function is implemeted for us in `Esendex_Sms_Model_EventProcessor_Abstract`. It does nothing by default. It allows 
for post processing to be done on the message after the variables have been interpolated. For example, you may always want to add a 
static piece of text to the end of the message like your company name or a website address. In our example we will stick with the default.

#### getStoreId()
Notifications are created and assigned to stores, so the `getStoreId` method is used to determine if there are notifications 
created in the admin panel for the specific context. For example, if an order has been created in store 1, and there is an `Order Created Notification` setup for
store 1, the event processor for that event will be executed. You decide where store ID comes from. In this example it will come from the customer. For the order events this will come from the order, e.g. The store the order was placed in. Our implementation will be:

```php
public function getStoreId()
{
    return $this->parameters->getData('customer')
        ->getStoredId();
}

```

This completes our implementation of the `Event Processor` for our custom notification.

This is what our full implementation should look like:

```php
<?php

/**
 * Class EsendexCustomNotifications_RegisterSuccessNotification_Model_CustomerRegisterSuccess
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class EsendexCustomNotifications_RegisterSuccessNotification_Model_EventProcessor_CustomerRegisterSuccess
    extends Esendex_Sms_Model_EventProcessor_Abstract
    implements Esendex_Sms_Model_EventProcessor_Interface
{
    /**
     * @var array
     */
    protected $variables = [
        'customer::firstname'   => 'firstname',
        'customer::lastname'    => 'lastname',
        'customer::email'       => 'email',
    ];

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

        return true;
    }

    /**
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     * @return string
     */
    public function getRecipient(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        $addresses = $this->parameters
            ->getData('customer')
            ->getAddresses();

        return $addresses[0]->getTelephone();
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->parameters->getData('customer')
            ->getStoredId();
    }

    /**
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     * @return Varien_Object
     */
    public function getVariableContainer(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        $this->parameters->setData(
            'store_name',
            Mage::getStoreConfig('general/store_information/name',
                $this->parameters->getData('customer')->getStoredId()
            )
        );
        return $this->parameters;
    }
}
```

Notice the `$variables` variable. This is what tells the Esendex module which variables are available for your message. 
It uses this property to build the `Add new Notification` form in the admin panel. 

### Add the notification
In order for the Esendex module to know about our new Event we need to tell it. We do this by adding an entry to the database.  

#### Create setup config

```xml
<config>
    <global>
        ...
        <resources>
            <esendexcustomnotifications_registersuccessnotification_setup>
                <setup>
                    <module>EsendexCustomNotifications_RegisterSuccessNotification</module>
                    <class>Esendex_Sms_Model_Resource_Setup</class>
                </setup>
            </esendexcustomnotifications_registersuccessnotification_setup>
        </resources>
        ...
    </global>
</config>
```

Add a setup script using the helper methods to insert a new event

```php
/** @var $this Esendex_Sms_Model_Resource_Setup */
$this->startSetup();


$this->addEvent(
    'Customer Register Success',
    'event',
    'customer_register_success',
    0,
    'esendexCustomNotifications_registerSuccessNotification/eventProcessor_customerRegisterSuccess'
);

$this->endSetup();

```

Note the function signature:

```php
public function addEvent($name, $triggerType, $triggerCode, $eventProcessorModel = null, $saveModel = null);
```

* Name is the name of the event to be displayed in the admin panel.
* Trigger type is the type of notification. There are two types with the current module implementation. `cron` and `event`. We only need to know about `event` for our implementation. The notification type of `event` maps this event to an internal Magento Event. 
* Trigger code is either the cron or event name we are hooking in to.
* Event Processor Model is the model class which we created previously. 
* Save Model is an optional parameter which can be used to override the save model for the notification, which by default, uses `Esendex_Sms_Model_Trigger`

#### Register an event
In order for the Esendex module to listen to the `customer_register_success` event we need to explicity set that up. Add the following to your modules `config.xml` file.

```xml
<config>
    <global>
    	...
        <events>
            <customer_register_success>
                <observers>
                    <customer_register>
                        <class>Esendex_Sms_Model_Observer</class>
                        <method>dispatchEvent</method>
                    </customer_register>
                </observers>
            </customer_register_success>
        </events>
    	...
    </global>
</config>
```

If you navigate to the admin panel now and click `Add New Notification` under the `SMS` menu, you should see your new event in the event menu. 
If you create the create the notification, specifying the message and sender and then register as a user at the site, entering a telephone number, 
you will receive a text message shortly after!

### Extras
1. If your Event Processor implements `Esendex_Sms_Model_Logger_LoggerAwareInterface`, `setLogger` will be passed an instance of `Psr\Log\LoggerInterface`. You can use this to log any errors that occur to the Esendex Sms logger. 

2. `getRecipient` can return either one telephone number as a string or an array of telephone numbers. If an array is 
returned the message will be sent to each of the recipients. 



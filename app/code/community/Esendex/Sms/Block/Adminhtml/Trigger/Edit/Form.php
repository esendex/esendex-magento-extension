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

use Esendex\Http\HttpException;

/**
 * Class Esendex_Sms_Block_Adminhtml_Trigger_Edit_Tab_Form
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Sms_Block_Adminhtml_Trigger_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * The current trigger data
     * @var array
     */
    protected $currentData = array();

    /**
     * The label for the Form Fieldset
     *
     * @var string
     */
    protected $notificationLegend = 'Notification';

    /**
     * Magento Construct, create initial form
     *
     * @throws Exception
     */
    protected function _construct()
    {
        parent::_construct();

        $this->currentData  = $this->getCurrentValues();
        $actionUrl          = $this->isStage2() ? '*/*/save' : '*/*/edit';
        $action             = $this->getUrl($actionUrl, array('id' => $this->getRequest()->getParam('id')));

        $this->_form = new Varien_Data_Form(array(
            'id'                => 'edit_form',
            'action'            => $action,
            'method'            => 'post',
            'html_id_prefix'    => 'trigger_',
            'field_name_suffix' => 'trigger'
        ));
    }

    /**
     * Try and find a sample message.
     * 1. If all of the selected stores share the same locale:
     *      * Try find a sample message using that locale for the given event
     *      * If none available, use the admin locale to find a message
     *      * If none available, try find an english sample message
     *
     * 2. If all the selected stores do not share the same locale:
     *      * Use the admin locale to find a message
     *      * If none available, try find an english sample message
     *
     * @param array $stores
     * @param int   $eventId
     *
     * @return string
     */
    public function getMessageTemplate(array $stores, $eventId)
    {
        $locales = array();
        foreach ($stores as $storeId) {
            $locales[$storeId] = Mage::getStoreConfig('general/locale/code', $storeId);
        }

        /** @var Esendex_Sms_Model_Resource_SampleMessage_Collection $collection */
        $collection = Mage::getModel('esendex_sms/sampleMessage')->getCollection();
        $collection->addEventFilter($eventId);

        //if there is only one locale in the stores selected
        if (count(array_unique($locales)) === 1) {
            $locale = reset($locales);
            if ($row = $collection->getItemByColumnValue('locale_code', $locale)) {
                return $row->getMessageTemplate();
            }
        }

        //try use admin locale
        $adminLocale = Mage::app()->getLocale()->getLocaleCode();
        if ($row = $collection->getItemByColumnValue('locale_code', $adminLocale)) {
            return $row->getMessageTemplate();
        }

        //try use english locale
        if ($row = $collection->getItemByColumnValue('locale_code', 'en_GB')) {
            return $row->getMessageTemplate();
        }

        //nope
        return '';
    }

    /**
     * Prepare the form
     *
     * @return Esendex_Sms_Block_Adminhtml_Trigger_Edit_Form
     */
    protected function _prepareForm()
    {
        // Add stage 1 field set
        $fieldset = $this->getForm()->addFieldset('form', array(
            'legend' => Mage::helper('esendex_sms')->__($this->notificationLegend)
        ));

        if (!$this->isStage2()) {
            $this->prepareStage1($fieldset);
        } else {
            $this->prepareStage2($fieldset);
        }

        // Set current form values without overriding defaults
        foreach ($this->currentData as $id => $value) {
            $element = $this->getForm()->getElement($id);
            if ($element) {
                $element->setValue($value);
            }
        }

        $this->getForm()->setUseContainer(true);
    }

    /**
     * @param Varien_Data_Form_Element_Fieldset $stage1Fieldset
     */
    protected function prepareStage1(Varien_Data_Form_Element_Fieldset $stage1Fieldset)
    {
        // Get events, current data and check if we need to disable event select
        $events = $this->getEvents()->toOptionArray();

        // Translate the events
        $events = array_map(function ($event) {
            $event['label'] = Mage::helper('esendex_sms')->__($event['label']);
            return $event;
        }, $events);

        // Add placeholder option
        array_unshift($events, array(
            'label' => Mage::helper('esendex_sms')->__('Please Select Event'),
            'value' => 0
        ));

        $stage1Fieldset->addField('event_id', 'select', array(
            'label'     => Mage::helper('esendex_sms')->__('Event'),
            'name'      => 'event_id',
            'required'  => true,
            'class'     => 'required-entry',
            'values'    => $events,
        ));

        // Only add store selector if we have multiple stores
        if (Mage::app()->isSingleStoreMode()) {
            // Add hidden field with store id value
            $stage1Fieldset->addField('stores', 'hidden', array(
                'name'  => 'stores[]',
                'value' => Mage::app()->getStore(true)->getId()
            ));
            return;
        }

        // Get store values for multiselect
        $stores = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);

        // Add multiselect field for stores
        $field = $stage1Fieldset->addField('store_id', 'multiselect', array(
            'name'     => 'stores[]',
            'label'    => Mage::helper('esendex_sms')->__('Store Views'),
            'title'    => Mage::helper('esendex_sms')->__('Store Views'),
            'required' => true,
            'values'   => $stores,
        ));

        // Use store switcher field rendered
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
    }

    /**
     * Prepares any stage 2 areas of the form
     *
     * @param Varien_Data_Form_Element_Fieldset $stage1Fieldset
     * @throws Exception
     */
    protected function prepareStage2(Varien_Data_Form_Element_Fieldset $stage1Fieldset)
    {
        // Get current event
        $event = $this->getEvent($this->currentData['event_id']);
        $defaultMessageTemplate = $this->getMessageTemplate(
            $this->currentData['store_id'],
            $this->currentData['event_id']
        );

        //if there is not a message already set (eg when we are editing)
        //set it as the default message
        if (!isset($this->currentData['message_template'])) {
            $this->currentData['message_template'] = $defaultMessageTemplate;
        }

        $message = 'This is who your message will show as being from on the receiving handset e.g. Mystore.';
        $note    = Mage::helper('esendex_sms')->__($message);
        $vmn     = $this->getAccountVirtualMobileNumber();

        if ($vmn) {
            $note .= '<br><br>' . $vmn;
        }

        // Add senders select
        $stage1Fieldset->addField('sender', 'text', array(
            'label'     => Mage::helper('esendex_sms')->__('From'),
            'name'      => 'sender',
            'required'  => true,
            'class'     => 'required-entry validate-sender-format',
            'note'      => $note,
            'value'    => 'Esendex'
        ));

        /** @var Esendex_Sms_Model_Resource_Event $event */
        $model = $event->getEventProcessor();

        $vars = array_map(function (Esendex_Sms_Model_Variable $var) {
            return $var->getReplaceName();
        }, $model->getVariables());
        //sort alphabetically
        sort($vars);

        $variables = sprintf('<li>%s</li>', Mage::helper('esendex_sms')->__('No Available Vars'));
        if (count($vars)) {
            $variables = '<li>' . implode("</li>\n<li>", $vars) . '</li>';
        }

        $link = 'http://support.esendex.co.uk/magento?i=magentosupport&ls=magento&sc=magentotriggervariables&sd=v1';
        $variableHelpLink  = '<p class="note" id="variable-help-link"><small>';
        $variableHelpLink .= Mage::helper('esendex_sms')->__(
            'For more information about using variables please visit our <a target="_blank" href="%s">support page</a>',
            $link
        );
        $variableHelpLink .= '</a></small></p>';

        $afterElementHtml = sprintf('<p class="note"><small>%s</small></p>',
            Mage::helper('esendex_sms')->__('These counts do not include variables.')
        );

        // Build html for message template textarea variables
        $afterElementHtml .= sprintf(
            '<td class="value"><h4>%s</h4><div id="available-variables-container""><ul id="available-variables">%s</ul></div>%s</td>',
            Mage::helper('esendex_sms')->__('Available Variables'),
            $variables,
            $variableHelpLink
        );

        //add default message template, for JavaScript to pickup
        $afterElementHtml .=
            sprintf('<td class="hidden" id="default-message-template">%s</td>', $defaultMessageTemplate);

        // Add message template textarea with available areas html
        $stage1Fieldset->addField('message_template', 'textarea', array(
            'label'                 => Mage::helper('esendex_sms')->__('Message'),
            'class'                 => 'validate-message-count validate-variables',
            'name'                  => 'message_template',
            'required'              => true,
            'after_element_html'    => $afterElementHtml,
        ));

        // Add description field
        $stage1Fieldset->addField('description', 'text', array(
            'label'     => Mage::helper('esendex_sms')->__('Description'),
            'name'      => 'description',
            'required'  => false,
        ));

        // Add status select
        $stage1Fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('esendex_sms')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'class'     => 'required-entry',
            'values'=> array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('esendex_sms')->__('Enabled'),
                ),
                array(
                    'value' => 0,
                    'label' => Mage::helper('esendex_sms')->__('Disabled'),
                ),
            ),
        ));

        // Stage 2 fieldset legend
        $legend = Mage::helper('esendex_sms')->__("'%s' specific fields", $event->getName());

        // Create stage 2 fieldset
        $stage2Fieldset = new Varien_Data_Form_Element_Fieldset(array(
            'legend'  => $legend,
            'html_id' => 'form_additional'
        ));

        // Add the form to the fieldset so the fields set their form correctly.
        $stage2Fieldset->setForm($this->getForm());

        // Dispatch Event With Empty Form Field set
        // This lets more complex event types can add their own fields
        $this->dispatchStage2Event($stage2Fieldset, $this->getSelectedEventName($event));

        // Add the field set to the form if it has been extended
        if (count($stage2Fieldset->getSortedElements())) {
            $this->getForm()->addElement($stage2Fieldset);
        }
    }

    /**
     * Dispatch stage 2 event to allow events to extend the form
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param string                            $eventName
     */
    protected function dispatchStage2Event(Varien_Data_Form_Element_Fieldset $fieldset, $eventName)
    {
        $eventName = 'esendex_sms_edit_form_stage2_' . $eventName;
        Mage::dispatchEvent($eventName, array('fieldset' => $fieldset));
    }

    /**
     * Get a nice name for the event
     *
     * @param Esendex_Sms_Model_Event $event
     * @return string
     */
    protected function getSelectedEventName(Esendex_Sms_Model_Event $event)
    {
        return strtolower(str_replace(' ', '_', $event->getName()));
    }

    /**
     * Get an event by its id
     *
     * @param int $eventId
     * @return Esendex_Sms_Model_Event
     * @throws Exception
     */
    protected function getEvent($eventId)
    {
        foreach ($this->getEvents() as $event) {
            if ($event->getId() === $eventId) {
                return $event;
            }
        }

        throw new \Exception(sprintf('Event with id: "%s" not found', $eventId));
    }

    /**
     * Check if form is currently in stage 2
     *
     * @return bool
     */
    protected function isStage2()
    {
        return isset($this->currentData['event_id']) && $this->currentData['event_id'];
    }

    /**
     * Get the Trigger Events Collection from Cache
     *
     * @return Esendex_Sms_Model_Resource_Event_Collection
     */
    protected function getEvents()
    {
        return Mage::getSingleton('esendex_sms/event')
            ->getCollection()
            ->notMobileSalesReports();
    }

    /**
     * Get the current values for the form
     *
     * @return array
     */
    protected function getCurrentValues()
    {
        $values = Mage::getModel('esendex_sms/trigger')->getDefaultValues();
        if (!is_array($values)) {
            $values = array();
        }

        // Set default store id
        $values['store_id'] = array(Mage::app()->getStore(true)->getId());

        if (Mage::registry('current_trigger')) {
            $values = array_merge(
                $values,
                Mage::registry('current_trigger')->getData()
            );
        }

        if (isset($values['stores'])) {
            $values['store_id'] = $values['stores'];
            unset($values['stores']);
        }

        // Convert recipients from array to new line delimited string
        if (isset($values['recipients']) && is_array($values['recipients'])) {
            $values['recipients'] = implode("\n", $values['recipients']);
        }

        return $values;
    }

    /**
     * @return \Esendex\Model\Account
     */
    public function getEsendexAccount()
    {
        return Esendex_Sms_Model_Api_Factory::getAccount();
    }

    /**
     * Get Virtual Mobile Number Help Text
     *
     * @return string
     */
    protected function getAccountVirtualMobileNumber()
    {
        $note = '';
        try {
            $account = $this->getEsendexAccount();

            if (!$account) {
                return $note;
            }

            if (null !== $account->address() && "" !== $account->address()) {
                return Mage::helper('esendex_sms')->__(
                    'To receive replies this will need to be set to your virtual mobile number +%s',
                    $account->address()
                );
            }

            if (null !== $account->alias() && "" !== $account->alias()) {
                return Mage::helper('esendex_sms')->__(
                    'To receive replies you will need to purchase a virtual mobile number'
                );
            }

            $message  = 'Note: To prevent spamming the from address will be ignored for trial accounts. ';
            $message .= '<a href="%s">Purchase Credits</a> to upgrade your account';
            return Mage::helper('esendex_sms')->__(
                $message,
                'https://www.esendex.com/redirect?i=ecommerce&ls=magento&sc=connectstore&sd=v1&returnUri=%s'
            );
        } catch (HttpException $e) {
        }
        return $note;
    }
}

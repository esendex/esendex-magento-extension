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
 * Class Esendex_Sms_Block_Adminhtml_Trigger_Edit
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Block_Adminhtml_Trigger_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * @var array
     */
    protected $currentData = array();

    /**
     * Stage 2 Update Button Text
     *
     * @var string
     */
    protected $stage2UpdateButtonLabel = 'Save Notification';

    /**
     * Stage 1 Update Button Text
     *
     * @var string
     */
    protected $updateButtonLabel = 'Continue';

    /**
     * Delete Button Text
     *
     * @var string
     */
    protected $deleteButtonLabel = 'Delete Notification';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup  = 'esendex_sms';
        $this->_controller  = 'adminhtml_trigger';
        $this->currentData  = $this->getFormValues();
        $helper             = Mage::helper('esendex_sms');

        if ($this->isStage2()) {
            $this->_updateButton('save', 'label', $helper->__($this->stage2UpdateButtonLabel));
            $this->_updateButton('save', 'onclick', 'triggerForm.submit()');

            $this->_addButton(
                'saveandcontinue',
                array(
                    'label'     => $helper->__('Save and Continue Edit'),
                    'onclick'   => 'triggerForm.submitAndContinue()',
                    'class'     => 'save-and-continue',
                ),
                -100
            );

            $this->_formScripts[] = "
                function saveAndContinueEdit(){
                    editForm.submit($('edit_form').action+'back/edit/');
                }
            ";
        } else {
            $this->_updateButton(
                'save',
                'label',
                $helper->__($this->updateButtonLabel)
            );
        }

        $this->_updateButton(
            'delete',
            'label',
            $helper->__($this->deleteButtonLabel)
        );

        $this->_formScripts[] = "var triggerForm = new TriggerForm()";
    }

    /**
     * Check if the event is set
     *
     * @return bool
     */
    public function isStage2()
    {
        return isset($this->currentData['event_id']) && $this->currentData['event_id'];
    }

    /**
     * Get the edit form header
     *
     * @return string
     */
    public function getHeaderText()
    {
        $trigger = Mage::registry('current_trigger');

        if ($trigger && $trigger->getId()) {
            return Mage::helper('esendex_sms')->__(
                "Edit Notification '%s' for event '%s'",
                $this->escapeHtml($trigger->getDescription()),
                $this->escapeHtml($this->getEventName($trigger))
            );
        }

        if ($trigger->getEventId()) {
            return Mage::helper('esendex_sms')->__(
                "Add New Notification for Event '%s'",
                $this->escapeHtml($this->getEventName($trigger))
            );
        }

        return Mage::helper('esendex_sms')->__("Add New Notification");
    }

    /**
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     * @return string
     */
    public function getEventName(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        $event = Mage::getModel('esendex_sms/event')->load($trigger->getEventId());
        return $event->getName();
    }

    /**
     * @return array
     */
    protected function getFormValues()
    {
        $formValues = $this->getRequest()->getPost('trigger');
        if (!is_array($formValues)) {
            $formValues = array();
        }

        if (Mage::registry('current_trigger')) {
            $formValues = array_merge(
                $formValues,
                Mage::registry('current_trigger')->getData()
            );
            return $formValues;
        }
        return $formValues;
    }
}

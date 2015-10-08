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
 * Class Esendex_Sms_Adminhtml_Sms_TriggerController
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Adminhtml_Sms_TriggerController extends Mage_Adminhtml_Controller_Action
{

    /**
     * The messages and titles used throught this class
     *
     * @var array
     */
    protected $messages = array(
        'index'                 => 'Manage Notifications',
        'not-exist'             => 'This notification no longer exists',
        'edit'                  => 'Edit Notification',
        'new'                   => 'Add Notification',
        'save-success'          => 'Notification was successfully saved',
        'save-error'            => 'There was a problem saving the notification',
        'delete-success'        => 'Notification was successfully deleted',
        'delete-error'          => 'There was an error deleting notification',
        'mass-delete-invalid'   => 'Please select Notifications to delete',
        'mass-delete-error'     => 'There was an error deleting notifications',
        'mass-delete-success'   => 'Total of %d notification%s %s successfully deleted',
        'mass-status-invalid'   => 'Please select Notifications',
        'mass-status-error'     => 'There was an error updating notifications',
        'mass-status-success'   => 'Total of %d notification%s %s successfully updated',
    );

    /**
     * File prefix
     *
     * @var string
     */
    protected $filePrefix = 'notifications';

    /**
     * @param int|null $eventId
     *
     * @return Esendex_Sms_Model_Trigger
     */
    public function loadNewTrigger($eventId = null)
    {
        $baseTriggerModel   = Mage::getModel('esendex_sms/trigger');
        $triggerModel       = $baseTriggerModel->getResource()->getTriggerModel($eventId);
        Mage::register('current_trigger', $triggerModel);
        return $triggerModel;
    }

    /**
     * @param int $triggerId
     *
     * @return Esendex_Sms_Model_Trigger
     */
    public function loadExistingTrigger($triggerId)
    {
        $baseTriggerModel   = Mage::getModel('esendex_sms/trigger');
        $triggerModel       = $baseTriggerModel->getResource()->load($baseTriggerModel, $triggerId);
        Mage::register('current_trigger', $triggerModel);
        return $triggerModel;
    }

     /**
     * Index action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('esendex_sms')->__('Esendex'))
             ->_title(Mage::helper('esendex_sms')->__($this->messages['index']));
        $this->renderLayout();
    }

    /**
     * Grid action
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * Edit trigger
     */
    public function editAction()
    {
        $triggerId  = $this->getRequest()->getParam('id');
        $data       = $this->getRequest()->getPost('trigger');

        if ($triggerId) {
            $trigger = $this->loadExistingTrigger($triggerId);
        } else {
            $trigger = $this->loadNewTrigger($data['event_id']);
        }

        if ($triggerId && !$trigger->getId()) {
            $this->_getSession()->addError(Mage::helper('esendex_sms')->__($this->messages['not-exist']));
            $this->_redirect('*/*/');
            return;
        }

        if ($data) {
            if (!isset($data['event_id']) || !isset($data['stores'])) {
                $this->_redirect('*/*/');
            }

            //if event_id is zero
            //form was submitted without selecting event type
            if ($data['event_id'] === "0") {

                $this->_getSession()->addError(Mage::helper('esendex_sms')->__('Please select the Event Type'));
                $this->_redirect('*/*/edit');
                return;
            }

            $this->saveStage1FormData($data);
            $trigger->setData($data);
        }

        $this->_title(Mage::helper('esendex_sms')->__('Esendex'));

        if ($trigger->getId()) {
            $this->_title(Mage::helper('esendex_sms')->__($this->messages['edit']));
        } else {
            $this->_title(Mage::helper('esendex_sms')->__($this->messages['new']));
        }

        $this->loadLayout()->renderLayout();
    }

    /**
     * @param array $data
     */
    public function saveStage1FormData(array $data)
    {
        $session = Mage::getSingleton('core/session');
        $session->setEsendexSmsStage1FormData(array(
            'event_id'  => $data['event_id'],
            'stores'    => $data['stores']
        ));
    }

    /**
     * @return array
     */
    public function getStage1FormData()
    {
        $session = mage::getSingleton('core/session');
        return $session->getEsendexSmsStage1FormData();
    }

    /**
     * New trigger action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save trigger action
     */
    public function saveAction()
    {
        $session = $this->_getSession();
        if (!$data = $this->getRequest()->getPost('trigger')) {
            return $this->_redirect('*/*/');
        }

        try {
            if ($this->getRequest()->getParam('id')) {
                $trigger = $this->loadExistingTrigger($this->getRequest()->getParam('id'));
            } else {
                $data       = array_merge($data, $this->getStage1FormData());
                $trigger     = $this->loadNewTrigger($data['event_id']);
            }
            $trigger->addData($data);

            //if validatable - do that magic
            if ($trigger instanceof Esendex_Sms_Model_ValidatableInterface) {
                if (!$trigger->validate()) {
                    foreach ($trigger->getErrors() as $error) {
                        $session->addError($error);
                    }

                    return $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                }
            }

            $trigger->save();
            $session->addSuccess(Mage::helper('esendex_sms')->__($this->messages['save-success']));
            $session->setFormData(false);

            if ($this->getRequest()->getParam('back')) {
                return $this->_redirect('*/*/edit', ['id' => $trigger->getId()]);
            }

            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
            $session->setTriggerData($data);
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        } catch (Exception $e) {
            Mage::logException($e);
            $session->addError(Mage::helper('esendex_sms')->__($this->messages['save-error']));
            $session->setTriggerData($data);
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
    }

    /**
     * Delete trigger action
     */
    public function deleteAction()
    {
        if (!$this->getRequest()->getParam('id')) {
            return $this->_redirect('*/*/');
        }

        try {
            $id = $this->getRequest()->getParam('id');
            Mage::getModel('esendex_sms/trigger')
                ->setId($id)
                ->delete();

            $this->_getSession()
                ->addSuccess(Mage::helper('esendex_sms')->__($this->messages['delete-success']));

            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()
                ->addError($e->getMessage());

            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        } catch (Exception $e) {
            $this->_getSession()
                ->addError(Mage::helper('esendex_sms')->__($this->messages['delete-error']));

            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            Mage::logException($e);
        }
    }

    /**
     * Mass delete trigger
     */
    public function massDeleteAction()
    {
        $triggerIds = $this->getRequest()->getParam('trigger');
        if (!is_array($triggerIds)) {
            $this->_getSession()->addError(Mage::helper('esendex_sms')->__($this->messages['mass-delete-invalid']));
            return $this->_redirect('*/*/index');
        }

        try {
            foreach ($triggerIds as $triggerId) {
                $trigger = Mage::getModel('esendex_sms/trigger');
                $trigger->setId($triggerId)->delete();
            }
            $count = count($triggerIds);
            $this->_getSession()->addSuccess(Mage::helper('esendex_sms')->__(
                sprintf(
                    $this->messages['mass-delete-success'],
                    $count > 1 ? 's' : '',
                    $count > 1 ? 'were' : 'was'
                ),
                $count
            ));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('esendex_sms')->__($this->messages['mass-delete-error']));
            Mage::logException($e);
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Mass status change
     */
    public function massStatusAction()
    {
        $triggerIds = $this->getRequest()->getParam('trigger');
        if (!is_array($triggerIds)) {
            $this->_getSession()->addError(Mage::helper('esendex_sms')->__($this->messages['mass-status-invalid']));
            return $this->_redirect('*/*/index');
        }

        try {
            foreach ($triggerIds as $triggerId) {
                Mage::getModel('esendex_sms/trigger')
                    ->setId($triggerId)
                    ->setStatus($this->getRequest()->getParam('status'))
                    ->save();
            }
            $count = count($triggerIds);
            $this->_getSession()->addSuccess(Mage::helper('esendex_sms')->__(
                $this->messages['mass-status-success'],
                $count,
                $count > 1 ? 's' : '',
                $count > 1 ? 'were' : 'was'
            ));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('esendex_sms')->__($this->messages['mass-status-error']));
            Mage::logException($e);
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export as csv - action
     */
    public function exportCsvAction()
    {
        $fileName   = sprintf('%s.csv', $this->filePrefix);
        $content    = $this->getLayout()->createBlock('esendex_sms/adminhtml_trigger_grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     */
    public function exportExcelAction()
    {
        $fileName   = sprintf('%s.xls', $this->filePrefix);
        $content    = $this->getLayout()->createBlock('esendex_sms/adminhtml_trigger_grid')->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     */
    public function exportXmlAction()
    {
        $fileName   = sprintf('%s.xml', $this->filePrefix);
        $content    = $this->getLayout()->createBlock('esendex_sms/adminhtml_trigger_grid')->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('esendex');
    }
}

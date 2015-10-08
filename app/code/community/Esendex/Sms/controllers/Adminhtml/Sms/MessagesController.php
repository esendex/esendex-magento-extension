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
 * Class Esendex_Sms_Adminhtml_Sms_MessagesController
 *
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Sms_Adminhtml_Sms_MessagesController extends Mage_Adminhtml_Controller_Action
{
     /**
     * Index action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('esendex_sms')->__('Esendex'))
             ->_title(Mage::helper('esendex_sms')->__('Sent Messages'));
        try {
            $this->renderLayout();
        } catch (HttpException $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                'Error getting sent messages, please check your credentials'
            );
            $this->_redirect('adminhtml/system_config/edit/section/esendex_sms');
        }
    }

    /**
     * Grid action
     */
    public function gridAction()
    {
        try {
            $this->loadLayout()->renderLayout();
        } catch (HttpException $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                'Error getting sent messages, please check your credentials'
            );
            $this->_redirect('adminhtml/system_config/edit/section/esendex_sms');
        }
    }

    /**
     * Check if admin has permissions to visit related pages
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/esendex_sms/messages');
    }
}

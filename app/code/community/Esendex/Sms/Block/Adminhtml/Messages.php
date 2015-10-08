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
 * Class Esendex_Sms_Block_Adminhtml_Messages
 *
 * @author Michael Woodward <michael@wearejh.com>
 */
class Esendex_Sms_Block_Adminhtml_Messages extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_controller  = 'adminhtml_messages';
        $this->_blockGroup  = 'esendex_sms';
        parent::__construct();
        $this->_headerText  = Mage::helper('esendex_sms')->__('Sent Messages');
        $this->_removeButton('add');
    }
}

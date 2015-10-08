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
 * Class Esendex_Sms_Block_Adminhtml_AccountNotifications
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Block_Adminhtml_AccountNotifications extends Mage_Core_Block_Template
{
    /**
     * @var string
     */
    protected $logo = 'http://www.esendex.co.uk/blog/wp-content/uploads/2010/06/Esendex-Stacked-Logotype-Primary-MASTER-300x212.jpg';

    /**
     * Get the logo for these notifications
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        $accountNotifications = Mage::getModel('esendex_sms/accountNotifications');
        return $accountNotifications->getNotifications();
    }
}

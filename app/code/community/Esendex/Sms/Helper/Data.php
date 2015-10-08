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
 * Class Esendex_Sms_Helper_Data
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return bool
     */
    public function hasNotFilledInAccountDetails()
    {
        return $this->isFieldFieldEmpty(Mage::getStoreConfig('esendex_sms/sms/account_reference'))
            && $this->isFieldFieldEmpty(Mage::getStoreConfig('esendex_sms/sms/email'))
            && $this->isFieldFieldEmpty(Mage::getStoreConfig('esendex_sms/sms/password'));
    }

    /**
     * @param string $field
     * @return bool
     */
    private function isFieldFieldEmpty($field)
    {
        return null === $field || '' === $field;
    }
} 
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
 * Class Esendex_Sms_Model_Resource_SampleMessage_Collection
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Resource_SampleMessage_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('esendex_sms/trigger');
    }

    /**
     * @param int $eventId
     */
    public function addEventFilter($eventId)
    {
        $this->addFieldToFilter('event_id', array('eq' => (int) $eventId));
    }
}

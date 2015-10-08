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
 * Class Esendex_Sms_Model_SampleMessage
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_SampleMessage extends Mage_Core_Model_Abstract
{
    const ENTITY    = 'esendex_sms_sample_message';
    const CACHE_TAG = 'esendex_sms_sample_message';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'esendex_sms_sample_message';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'sample_message';

    /**
     * Constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('esendex_sms/sampleMessage');
    }
}

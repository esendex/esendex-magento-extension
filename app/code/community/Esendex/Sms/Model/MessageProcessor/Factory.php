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
 * Class Esendex_Sms_Model_MessageProcessor_Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_MessageProcessor_Factory
{

    /**
     * @return Esendex_Sms_Model_MessageProcessor_MessageProcessor
     */
    public static function getInstance()
    {
        $logger = Esendex_Sms_Model_Logger_Factory::getInstance();
        return new Esendex_Sms_Model_MessageProcessor_MessageProcessor(
            $logger,
            new Esendex_Sms_Model_MessageInterpolator($logger)
        );
    }
}

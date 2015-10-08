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
 * Class Esendex_Sms_Model_EventProcessor_Interface
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface Esendex_Sms_Model_EventProcessor_Interface
{
    /**
     * The variable this message can process
     *
     * @return Esendex_Sms_Model_Event_Variable[]
     */
    public function getVariables();

    /**
     * @param Varien_Object $parameters
     *
     * @return void
     */
    public function setParameters(Varien_Object $parameters);

    /**
     * @return Varien_Object
     */
    public function getVariableContainer(Esendex_Sms_Model_TriggerAbstract $trigger);

    /**
     * Whether or not a message needs to be sent, based on the current runtime data (event)
     *
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     *
     * @return bool
     */
    public function shouldSend(Esendex_Sms_Model_TriggerAbstract $trigger);

    /**
     * Get the recipients or recipient.
     *
     * @param Esendex_Sms_Model_TriggerAbstract $trigger
     *
     * @return array|string
     */
    public function getRecipient(Esendex_Sms_Model_TriggerAbstract $trigger);

    /**
     * The replaced message will be passed to this
     * function where the implementer can do some finall
     * post-processing on the message, before it is sent
     *
     * @param string $message
     *
     * @return string
     */
    public function postProcess($message);

    /**
     * Get the store ID associated with this event
     * EG: If an order, it should return the store ID the
     * order was placed in
     *
     * @return int
     */
    public function getStoreId();
}

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
 * Class Esendex_Sms_Model_EventProcessor_Abstract
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
abstract class Esendex_Sms_Model_EventProcessor_Abstract
{
    /**
     * @var array|Esendex_Sms_Model_Event_Variable[]
     */
    protected $variables = [];

    /**
     * @var Varien_Object
     */
    protected $parameters = [];

    /**
     * Check variables
     */
    public function __construct()
    {
        if (!is_array($this->variables)) {
            throw new \RuntimeException(
                'The property: "variables" must be an array of available variables'
            );
        }

        $variables = [];
        foreach ($this->variables as $path => $name) {
            $variables[] = new Esendex_Sms_Model_Variable($name, $path);
        }

        $this->variables = $variables;
    }

    /**
     * @param Varien_Object $parameters
     */
    public function setParameters(Varien_Object $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return Esendex_Sms_Model_Event_Variable[]
     */
    final public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return Varien_Object
     */
    public function getVariableContainer(Esendex_Sms_Model_TriggerAbstract $trigger)
    {
        $data = [];
        if ($this->parameters instanceof Varien_Object) {
            $data = $this->parameters->getData();
        }

        //here you can either create your own array of variables
        //or use ones set for you - or merge them!becuase we
        $container = new Varien_Object($data);
        return $container;
    }

    /**
     * @param string $message
     * @return string
     */
    public function postProcess($message)
    {
        return $message;
    }
}

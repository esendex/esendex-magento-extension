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
use Psr\Log\LoggerInterface;

/**
 * Class Esendex_Sms_Model_MessageInterpolator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_MessageInterpolator
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string                                $messageTemplate
     * @param Varien_Object                         $variableContainer
     * @param Esendex_Sms_Model_Event_Variable[]    $variables
     *
     * @return string
     */
    public function interpolate($messageTemplate, Varien_Object $variableContainer, array $variables)
    {
        $parsedVariables = [];

        foreach ($variables as $variable) {
            /** @var $variable Esendex_Sms_Model_Variable */
            $variableName   = $variable->getReplaceName();
            $parts          = $variable->getPathParts();

            $depth          = count($parts);
            $objectToSearch = clone $variableContainer;
            for ($i = 0; $i < $depth; $i++) {
                $key = array_shift($parts);

                if (count($parts)) {
                    if ($objectToSearch instanceof Varien_Object && $objectToSearch->hasData($key)) {
                        $objectToSearch = $objectToSearch->getData($key);
                    } else {
                        $this->logNotFoundVariable($key, $variableName, $objectToSearch);
                        $parsedVariables[$variableName] = "";
                        break;
                    }
                } else {
                    //this is the resulting variable we want to interpolate
                    if ($objectToSearch instanceof Varien_Object && $objectToSearch->hasData($key)) {
                        //add it to the array of vars to be replaced in the message
                        $parsedVariables[$variableName] = $objectToSearch->getData($key);
                    } else {
                        //var doesn't exist so just keep the placeholder so receiver knows something went wrong.
                        $this->logNotFoundVariable($key, $variableName, $objectToSearch);
                        $parsedVariables[$variableName] = "";
                    }
                }
            }
        }

        $message = $messageTemplate;
        foreach ($parsedVariables as $search => $replace) {
            if (!is_scalar($replace)) {
                $this->logger->error(
                    sprintf(
                        'Cannot replace placeholder with a non-scalar value (Eg, String, Integer). Got: "%s"',
                        is_object($replace) ? get_class($replace) : gettype($replace)
                    )
                );
                $replace = "";
            }

            if ($replace === "") {
                //if the variable didn't exist we want to remove the placeholder from the message

                //match the placeholder and 1 optional space before it
                //so optional variables like lastname don't leave extra spaces
                //eg. 'Hello $FIRST$ $LAST$,' would result it in 'Hello $FIRST$ ,' <- Note the extra space

                //preg quote escapes the dollar signs in the variable name as dollars are part of the regex syntax.
                $message = preg_replace(sprintf('/(\s)?%s/', preg_quote($search)), "", $message);
            } else {
                //the variable was found so just do a straight replace
                $message = str_replace($search, $replace, $message);
            }
        }

        return $message;
    }

    /**
     * @param string $variablePath
     * @param string $variableName
     * @param mixed $variable
     */
    public function logNotFoundVariable($variablePath, $variableName, $variable)
    {
        $got    = is_object($variable) ? get_class($variable) : gettype($variable);
        $type   = is_object($variable) ? 'object' : 'type';

        $this->logger->debug(
            sprintf(
                'Could not find variable: "%s" with path: "%s" on %s %s',
                $variableName,
                $variablePath,
                $type,
                $got
            )
        );
    }
}

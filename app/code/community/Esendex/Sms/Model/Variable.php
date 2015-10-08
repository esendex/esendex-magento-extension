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
 * Class Esendex_Sms_Model_Variable
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Esendex_Sms_Model_Variable
{

    /**
     * @var string
     */
    const VAR_NAME_REGEX = '/^[a-zA-Z]+[a-zA-Z\d_]*$/';

    /**
     * Regex for validating variable path parts
     */
    const VAR_PATH_PART_REGEX = '/^[a-zA-Z]+[a-zA-Z\d_]*$/';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @param string $variablePath
     * @return bool|array
     */
    public static function validateVariablePath($variablePath)
    {
        $parts = explode("::", $variablePath);

        foreach ($parts as $part) {
            if (!preg_match(static::VAR_PATH_PART_REGEX, $part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $name
     * @param string $path
     */
    public function __construct($name, $path)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(
                sprintf('Name should be a string. Given: "%s"', is_object($name) ? get_class($name) : gettype($name))
            );
        }

        if (!is_string($path)) {
            throw new \InvalidArgumentException(
                sprintf('Path should be a string. Given: "%s"', is_object($path) ? get_class($path) : gettype($path))
            );
        }

        if (!static::validateVariablePath($path)) {
            throw new \InvalidArgumentException(
                sprintf('Variable path should be in format: "obj::var::var". Given: "%s"', $path)
            );
        }

        if (!preg_match(static::VAR_NAME_REGEX, $name)) {
            throw new \InvalidArgumentException(
                sprintf('Name should be in format: "%s"', static::VAR_NAME_REGEX)
            );
        }

        $this->name = $name;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getReplaceName()
    {
        return '$' . strtoupper($this->name) . '$';
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the path as an array of parts
     *
     * @return array
     */
    public function getPathParts()
    {
        return explode("::", $this->path);
    }
}
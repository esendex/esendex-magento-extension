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
 * Class VariableTest
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class VariableTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider invalidVariablePathProvider
     * @param string $path
     */
    public function testInvalidVariablePathReturnsFalse($path)
    {
        $message = sprintf('Variable path should be in format: "obj::var::var". Given: "%s"', $path);
        $this->setExpectedException('InvalidArgumentException', $message);
        new Esendex_Sms_Model_Variable('name', $path);
    }

    public function invalidVariablePathProvider()
    {
        return [
            ['1142'],
            ['123:tc'],
            ['someobj:somevar'],
            ['someobj::%nope'],
        ];
    }

    /**
     * @dataProvider validVariablePathProvider
     *
     * @param string $path
     * @param array $parts
     */
    public function testValidVariables($path, $parts)
    {
        $var = new Esendex_Sms_Model_Variable('name', $path);

        $this->assertEquals('$NAME$', $var->getReplaceName());
        $this->assertEquals($path, $var->getPath());
        $this->assertEquals($parts, $var->getPathParts());
    }

    public function validVariablePathProvider()
    {
        return [
            ['somevar',                             ['somevar']],
            ['someobj::somevar',                    ['someobj', 'somevar']],
            ['someobj::someotherobj::somevar',      ['someobj', 'someotherobj', 'somevar']],
        ];
    }

    public function testExceptionIsThrownIfNameIsNotAString()
    {
        $message = 'Name should be a string. Given: "stdClass"';
        $this->setExpectedException('InvalidArgumentException', $message);
        new Esendex_Sms_Model_Variable(new stdClass, 'lol');
    }

    public function testExceptionIsThrownIfPathIsNotAString()
    {
        $message = 'Path should be a string. Given: "stdClass"';
        $this->setExpectedException('InvalidArgumentException', $message);
        new Esendex_Sms_Model_Variable('lol', new stdClass);
    }

    public function invalidNameProvider()
    {
        return [
            ['1142'],
            ['123:tc'],
            ['someobj:somevar'],
            ['someobj::%nope'],
        ];
    }

    /**
     * @dataProvider invalidNameProvider
     *
     * @param string $name
     */
    public function testInvalidVarNameThrowsException($name)
    {
        $message = 'Name should be in format: "/^[a-zA-Z]+[a-zA-Z\d_]*$/"';
        $this->setExpectedException('InvalidArgumentException', $message);
        new Esendex_Sms_Model_Variable($name, 'path');
    }
}
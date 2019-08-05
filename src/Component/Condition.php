<?php

namespace Bego\Component;

class Condition
{
    protected $_attribute;

    public function __construct($attribute)
    {
        $this->_attribute = $attribute;
    }

    public function eq($value)
    {
        return new Condition\Comperator($this->_attribute, '=', $value);
    }

    public function notEq($value)
    {
        return new Condition\Comperator($this->_attribute, '<>', $value);
    }

    public function gt($value)
    {
        return new Condition\Comperator($this->_attribute, '>', $value);
    }

    public function gtOrEq($value)
    {
        return new Condition\Comperator($this->_attribute, '>=', $value);
    }

    public function lt($value)
    {
        return new Condition\Comperator($this->_attribute, '<', $value);
    }

    public function ltOrEq($value)
    {
        return new Condition\Comperator($this->_attribute, '<=', $value);
    }

    public function beginsWith($value)
    {
        return new Condition\BeginsWith($this->_attribute, $value);
    }

    public function exists($flag = true)
    {
        return $flag === true 
            ? new Condition\AttributeExists($this->_attribute)
            : new Condition\AttributeNotExists($this->_attribute);
    }

    public function contains($value)
    {
        return new Condition\Contains($this->_attribute, $value);
    }

    public function notContains($value)
    {
        return new Condition\NotContains($this->_attribute, $value);
    }

    public function in($values)
    {
        return new Condition\In($this->_attribute, $values);
    }

    public static function and($conditions)
    {
        $statements = [];

        foreach ($conditions as $condition) {
            $statements[] = $condition->statement();
        }

        return implode(' and ', $statements);
    }

    public static function or($conditions)
    {
        $statements = [];

        foreach ($conditions as $condition) {
            $statements[] = $condition->statement();
        }

        return implode(' or ', $statements);
    }
}
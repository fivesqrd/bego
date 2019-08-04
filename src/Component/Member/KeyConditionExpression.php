<?php

namespace Bego\Component\Member;

use Bego\Component\Condition;

class KeyConditionExpression
{
    protected $_conditions = [];

    public function __construct($conditions)
    {
        $this->_conditions = $conditions;
    }

    public function isDefined()
    {
        return count($this->_conditions) > 0;
    }

    public function getParameterKey()
    {
        return 'KeyConditionExpression';
    }

    public function statement()
    {
        return Condition::and($this->_conditions);
    }

    public function names()
    {
        $names = [];

        foreach ($this->_conditions as $condition) {
            if (is_array($condition->name())) {
                $names = array_merge($names, $condition->name());
            } else {
                $names[] = $condition->name();
            }
        }

        return $names;
    }

    public function values()
    {
        $values = [];

        foreach ($this->_conditions as $condition) {
            $values = array_merge($values, $condition->values());
        }

        return $values;
    }
}
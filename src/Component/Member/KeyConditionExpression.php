<?php

namespace Bego\Component\Member;

use Bego\Exception as BegoException;
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
        if (count($this->_conditions) > 2) {
            throw new BegoException(
                'Maximum of 2 key condition expressions are allowed'
            );
        }

        /* todo: Validate expression as per: https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/Query.html#Query.KeyConditionExpressions */

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
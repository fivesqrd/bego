<?php

namespace Bego\Component\Member;

class AttributeNames
{
    protected $_expressions = [];

    public function __construct($expressions)
    {
        $this->_expressions = $expressions;
    }

    public function isDefined()
    {
        return count($this->_expressions) > 0;
    }

    public function getParameterKey()
    {
        return 'ExpressionAttributeNames';
    }

    public function statement()
    {
        $names = [];

        foreach ($this->_expressions as $expression) {

            if (!$expression->isDefined()) {
                continue;
            }

            $names = array_merge($names, $expression->names());
        }

        return $names;
    }
}
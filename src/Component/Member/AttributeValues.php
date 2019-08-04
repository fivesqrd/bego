<?php

namespace Bego\Component\Member;

class AttributeValues
{
    protected $_marshaler;
    
    protected $_expressions = [];

    public function __construct($marshaler, $expressions)
    {
        $this->_marshaler = $marshaler;
        $this->_expressions = $expressions;
    }

    public function isDefined()
    {
        foreach ($this->_expressions as $expression) {
            if ($expression->isDefined()) {
                return true;
            }
        }

        return false;
    }

    public function getParameterKey()
    {
        return 'ExpressionAttributeValues';
    }

    public function statement()
    {
        $values = [];

        foreach ($this->_expressions as $expression) {

            if (!$expression->isDefined()) {
                continue;
            }

            if (empty($expression->values())) {
                continue;
            }

            $values = array_merge($values, $expression->values());
        }

        return $this->_marshaler->marshalJson(
            json_encode($values)
        );
    }
}
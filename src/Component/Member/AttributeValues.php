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
        return count($this->_getAggregatedValues()) > 0;
    }

    public function getParameterKey()
    {
        return 'ExpressionAttributeValues';
    }

    public function statement()
    {
        return $this->_marshaler->marshalJson(
            json_encode($this->_getAggregatedValues())
        );
    }

    protected function _getAggregatedValues()
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

        return $values;
    }
}
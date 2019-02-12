<?php

namespace Bego\Component;

/**
 * key condition expression
 */
class AttributeName
{
    protected $_attribute;

    public function __construct($attribute)
    {
        $this->_attribute = $attribute;
    }

    public function key()
    {
        return '#' . $this->_sanitise($this->_attribute);
    }

    public function placeholder($prefix = null)
    {
        return ':' . $this->_sanitise($prefix . $this->_attribute);
    }

    public function raw()
    {
        return $this->_attribute;
    }

    protected function _sanitise($value)
    {
        return preg_replace("/[^A-Za-z0-9 ]/", '', $value);
    }
}
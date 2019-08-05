<?php

namespace Bego\Component;

use Bego\Exception as BegoException;

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
        if (empty($value)) {
            throw new BegoException('Attribute name may not be empty');
        }

        return preg_replace("/[^A-Za-z0-9 ]/", '', $value);
    }
}
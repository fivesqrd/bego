<?php

namespace Bego\Component\Condition;

use Bego\Component\AttributeName;

/**
 * key condition expression
 */
class In
{
    protected $_attribute;

    protected $_values;

    const VALUABLE = true;
    const PREFIX = 'In';

    public function __construct($attribute, $values)
    {
        $this->_attribute = $attribute;
        $this->_values = $values;
    }

    public function attribute()
    {
        return $this->_attribute;
    }

    public function name()
    {
        return [$this->_attribute->key() => $this->_attribute->raw()];
    }

    public function statement()
    {
        $placeholders = implode(', ', array_keys($this->values()));
        return "{$this->_attribute->key()} in ($placeholders)";
    }

    public function values()
    {
        $values = [];

        foreach ($this->_values as $i => $value) {
            $key = $this->_attribute->placeholder(static::PREFIX) . $i;
            $values[$key] = $value;
        }

        return $values;
    }
}
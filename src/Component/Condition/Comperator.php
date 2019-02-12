<?php

namespace Bego\Component\Condition;

use Bego\Component\AttributeName;

/**
 * key condition expression
 */
class Comperator
{
    protected $_attribute;

    protected $_value;

    protected $_operator;

    protected $_valid = [
        '=', '<>', '<', '<=', '>', '>='
    ];

    const VALUABLE = true;
    const PREFIX = 'Cmp';

    public function __construct($attribute, $operator, $value)
    {
        $this->_attribute = $attribute;
        $this->_value = $value;
        $this->_operator = $operator;
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
        return $this->_attribute->key()
            . ' ' . $this->_operator
            . ' ' . $this->_attribute->placeholder(static::PREFIX);
    }

    public function values()
    {
        return [$this->_attribute->placeholder(static::PREFIX) => $this->_value];
    }
}
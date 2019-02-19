<?php

namespace Bego\Component\Condition;

use Bego\Component\AttributeName;

/**
 * key condition expression
 */
class NotContains
{
    protected $_attribute;

    protected $_value;

    const VALUABLE = true;
    const PREFIX = 'Nco';

    public function __construct($attribute, $value)
    {
        $this->_attribute = $attribute;
        $this->_value = $value;
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
        return "not(contains({$this->_attribute->key()}, {$this->_attribute->placeholder(static::PREFIX)}))";
    }

    public function values()
    {
        return [$this->_attribute->placeholder(static::PREFIX) => $this->_value];
    }
}

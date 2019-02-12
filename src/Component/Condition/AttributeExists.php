<?php

namespace Bego\Component\Condition;

use Bego\Component\AttributeName;

/**
 * key condition expression
 */
class AttributeExists
{
    protected $_attribute;

    const VALUABLE = FALSE;
    const PREFIX = 'Ae';

    public function __construct($attribute)
    {
        $this->_attribute = $attribute;
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
        return "attribute_exists({$this->_attribute->key()})";
    }

    public function values()
    {
        return [];
    }
}
<?php

namespace Bego\Scan;

use Bego\Component\AttributeName;

/**
 * key condition expression
 */
class Expression
{
    protected $_names = [];

    protected $_statements = [];

    protected $_values = [];

    public function __construct($items = [])
    {
        foreach ($items as $item) {
            $this->add(
                new AttributeName($item['name']), $item['operator'], $item['value']
            );
        }
    }

    public function isDirty()
    {
        return count($this->_values) > 0;
    }

    public function add(AttributeName $name, $operator, $value)
    {
        if (array_key_exists($name->placeholder(), $this->_values)) {
            throw new \Exception("{$name->raw()} cannot be used twice");
        }

        $this->_names[$name->key()] = $name->raw();

        array_push($this->_statements, $this->_statement($name, $operator));

        $this->_values[$name->placeholder()] = $value;

        return $this;
    }

    protected function _statement($attribute, $operator)
    {
        if ($this->_isFunction($operator)) {
            return "{$operator}({$attribute->key()}, {$attribute->placeholder()})";
        }

        return "{$attribute->key()} {$operator} {$attribute->placeholder()}";
    }

    protected function _isFunction($value)
    {
        $methods = ['begins_with'];

        return in_array($value, $methods);
    }

    public function values()
    {
        return $this->_values;
    }

    public function names()
    {
        return $this->_names;
    }

    public function statement()
    {
        return implode(' and ', $this->_statements);
    }
}

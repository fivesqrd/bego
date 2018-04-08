<?php

namespace Bego\Query;

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
                $item['field'], $item['operator'], $item['value']
            );
        }
    }

    public function isDirty()
    {
        return count($this->_values) > 0;
    }

    public function add($field, $operator, $value)
    {
        $name = '#' . $field;
        $placeholder = ':' . $field;

        if (array_key_exists($placeholder, $this->_values)) {
            throw new \Exception("{$field} cannot be used twice");
        }

        $this->_names[$name] = $field;

        array_push($this->_statements, "{$name} {$operator} {$placeholder}");

        $this->_values[$placeholder] = $value;

        return $this;
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
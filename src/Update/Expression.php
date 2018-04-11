<?php

namespace Bego\Update;

/**
 * key condition expression
 */
class Expression
{
    protected $_names = [];

    protected $_statements = [];

    protected $_values = [];

    public function __construct($attributes = [])
    {
        foreach ($attributes as $name => $value) {
            $this->add($name, $value);
        }
    }

    public function isDirty()
    {
        return count($this->_values) > 0;
    }

    public function add($name, $value)
    {
        $key = '#' . $name;
        $placeholder = ':' . $name;

        if (array_key_exists($placeholder, $this->_values)) {
            throw new \Exception("{$name} cannot be used twice");
        }

        $this->_names[$key] = $name;

        array_push($this->_statements, "{$key} = {$placeholder}");

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
        return 'SET ' . implode(', ', $this->_statements);
    }
}
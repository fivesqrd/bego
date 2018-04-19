<?php

namespace Bego\Update;

use Bego\Component\AttributeName;

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
        //TODO: Support SET and REMOVE actions
        foreach ($attributes as $name => $value) {
            $this->add(
                new AttributeName($item['name']), $value
            );
        }
    }

    public function isDirty()
    {
        return count($this->_values) > 0;
    }

    public function add(AttributeName $name, $value)
    {
        if (array_key_exists($name->placeholder(), $this->_values)) {
            throw new \Exception("{$name->raw()} cannot be used twice");
        }

        $this->_names[$name->key()] = $name->raw();

        array_push($this->_statements, "{$name->key()} = {$name->placeholder()}");

        $this->_values[$name->placeholder()] = $value;

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
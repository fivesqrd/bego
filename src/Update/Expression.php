<?php

namespace Bego\Update;

use Bego\Component\AttributeName;
use Bego\Item;
use Bego\Exception;

/**
 * key condition expression
 */
class Expression
{
    protected $_names = [];

    protected $_set = [];

    protected $_remove = [];

    protected $_values = [];

    public static function item(Item $item)
    {
        $instance = new static();

        foreach ($item->diff() as $diff) {
            $instance->action($diff);
        }

        return $instance;
    }

    public function isDirty()
    {
        return count($this->_values) > 0;
    }

    public function action($spec)
    {
        if ($spec['action'] == 'set') {
            return $this->set(
                new AttributeName($spec['attribute']), $spec['value']
            );
        }

        if ($spec['action'] == 'remove') {
            return $this->remove(
                new AttributeName($spec['attribute'])
            );
        }

        throw new Exception("Action '{$spec['action']}' not supported");
    }

    public function remove(AttributeName $name)
    {
        $this->_names[$name->key()] = $name->raw();

        array_push($this->_remove, "{$name->key()}");

        return $this;
    }

    public function set(AttributeName $name, $value)
    {
        if (array_key_exists($name->placeholder(), $this->_values)) {
            throw new \Exception("{$name->raw()} cannot be used twice");
        }

        $this->_names[$name->key()] = $name->raw();

        array_push($this->_set, "{$name->key()} = {$name->placeholder()}");

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
        $actions = [];

        if (count($this->_set) > 0) {
             $actions[] = 'SET ' . implode(', ', $this->_set);
        }

        if (count($this->_remove) > 0) {
             $actions[] = 'REMOVE ' . implode(', ', $this->_remove);
        }

        if (count($actions) == 0) {
            throw new Exception('No update actions to perform on this item');
        } 

        return implode(' ', $actions);
    }
}

<?php

namespace Bego;

class Item
{
    protected $_attributes = [];

    protected $_diff = [];

    public function __construct($attributes = [])
    {
        $this->_attributes = $attributes;
        $this->_init = $attributes;
    }

    public function get($key)
    {
        if (!array_key_exists($key, $this->_attributes)) {
            return null;
        }

        return $this->_attributes[$key];
    }

    public function set($key, $value)
    {
        if ($this->get($key) !== $value) {
            $this->_diff[$key] = $value;
        }

        $this->_attributes[$key] = $value;

        return $this;
    }

    public function diff()
    {
        return $this->_diff;
    }

    public function attributes()
    {
        return $this->_attributes;
    }

    public function clean()
    {
        $this->_diff = [];
    }

    public function isDirty()
    {
        return count($this->_diff) > 0;
    }
}
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

    public function isset($key)
    {
        return array_key_exists($key, $this->_attributes);
    }

    public function attribute($key)
    {
        //TODO: support dot notation of nested attributes
        
        if (!$this->isset($key)) {
            return null;
        }

        return $this->_attributes[$key];
    }

    public function set($key, $value)
    {
        if ($this->attribute($key) !== $value) {
            $this->_diff[] = [
                'attribute' => $key,
                'value'     => $value,
                'action'    => 'set'
            ];
        }

        $this->_attributes[$key] = $value;

        return $this;
    }

    public function remove($key)
    {
        if (!array_key_exists($key, $this->_attributes)) {
            return false;
        }

        $this->_diff[] = [
            'attribute' => $key,
            'action'    => 'remove'
        ];

        unset($this->_attributes[$key]);

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

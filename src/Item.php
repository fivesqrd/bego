<?php

namespace Bego;

class Item
{
    protected $_attributes = [];

    protected $_diff = [];

    public function __construct($attributes = [])
    {
        $this->_attributes = $attributes;
    }

    public function attribute($key)
    {
        //TODO: support dot notation of nested attributes
        
        if (!$this->isSet($key)) {
            return null;
        }

        return $this->_attributes[$key];
    }

    public function set($key, $value)
    {
        if ($value == '') {
            /* Empty strings are not allowed, convert to null */
            $value = null;
        }

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

    public function add($key, $value)
    {
        $this->_diff[] = [
            'attribute' => $key,
            'value'     => $value,
            'action'    => 'add'
        ];

        if (!is_array($this->_attributes[$key])) {
            $this->_attributes[$key] = [];
        }

        array_push($this->_attributes[$key], $value);

        return $this;
    }

    /**
     * Delete a member from a list
     */
    public function delete($key, $value)
    {
        if (!is_array($this->attribute($key))) {
            return false;
        }
        
        if (!in_array($this->attribute($key), $value)) {
            return false;
        }

        $this->_diff[] = [
            'attribute' => $key,
            'value'     => $value,
            'action'    => 'delete'
        ];

        $index = array_search($value, $this->attribute($key));

        unset($this->_attributes[$key][$index]);

        return $this;
    }

    /**
     * Remote attribute from item
     */
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

    public function isSet($key)
    {
        return array_key_exists($key, $this->_attributes);
    }

    public function isDirty()
    {
        return count($this->_diff) > 0;
    }

    public function isEmpty()
    {
        return empty($this->_attributes);
    }

    public function __get($key) 
    {
        return $this->attribute($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __isset($key)
    {
        return $this->isSet($key);
    }

    public function __unset($key)
    {
        return $this->remove($key);
    }
}

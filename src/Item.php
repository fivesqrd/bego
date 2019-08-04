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

    /** 
     * Return the an attribute's value. If attribute is present exception will be thrown
     * @param string $key
     * @return mixed
     */
    public function ping($key)
    {
        /* todo: support dot notation of nested attributes */
        
        if (!array_key_exists($key, $this->_attributes)) {
            throw new Exception(
                "Attribute '{$key}' is not present in this item"
            );
        }

        return $this->_attributes[$key];
    }

    /** 
     * Return the an attribute's value. If attribute is present return value is null
     * @param string $key
     * @return mixed
     */
    public function attribute($key)
    {
        /* todo: support dot notation of nested attributes */
        
        if (!array_key_exists($key, $this->_attributes)) {
            return null;
        }

        return $this->_attributes[$key];
    }

    /**
     * Set an attribute's value
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        if ($value == '') {
            /* Empty strings are not allowed, convert to null */
            $value = null;
        }

        if (!$this->isSet($key)) {
            /* First add an empty attribute to this item */
            $this->_attributes[$key] = null;
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

    /**
     * Add a member to a list attribute
     */
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
     * Delete a member from a list attribute
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
        if (!array_key_exists($key, $this->_attributes)) {
            return false;
        }

        if ($this->_attributes[$key] === null) {
            return false;
        }

        return true;
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

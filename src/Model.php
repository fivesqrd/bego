<?php

namespace Bego;

abstract class Model
{
    /**
     * Name of the table
     */
    protected $_name;

    /**
     * Table's partition key attribute
     */
    protected $_partition;

    /**
     * Table's sort key attribute(s)
     */
    protected $_sort = [];

    protected $_indexes = [];

    public function name()
    {
        return $this->_name;
    }

    public function partition()
    {
        return $this->_partition;
    }

    public function sort()
    {
        return $this->_sort;
    }

    public function index($key)
    {
        if (!array_key_exists($key, $this->_indexes)) {
            throw new \Exception("Index '{$key}' not defined in table model class");
        }

        return $this->_indexes[$key];
    }
}
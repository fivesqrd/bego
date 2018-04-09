<?php

namespace Bego\Query;

class Resultset implements \Iterator, \Countable
{
    protected $_pointer;

    protected $_key;

    protected $_marshaler;

    protected $_units = false;

    protected $_result = [];

    public function __construct($marshaler, $result)
    {
        $this->_marshaler = $marshaler;
        $this->_result = $result;
    }

    public function rewind()
    {
        $this->_pointer = 0;
    }

    public function key()
    {
        return $this->_pointer;
    }

    public function next()
    {
        $item = $this->item($this->_pointer);

        if ($item) {
            $this->_pointer++;
        }
        
        return $item;
    }

    public function first()
    {
        return $this->item(0);
    }

    public function last()
    {
        return $this->item(
            $this->count() - 1
        );
    }

    public function valid()
    {
        return (!is_null($this->current()));
    }

    public function current()
    {
        return $this->item($this->_pointer);
    }

    public function count()
    {
        return count($this->_result['Items']);
    }

    public function item($index)
    {
        if (!array_key_exists($index, $this->_result['Items'])) {
            return null;
        }

        return $this->_marshaler->unmarshalItem(
            $this->_result['Items'][$index]
        );
    }

    public function param($key, $default = null)
    {
        if (!array_key_exists($key, $this->_result) && $default === null) {
            throw new \Exception("Parameter key '{$key}' not found in result set");
        }

        if (!array_key_exists($key, $this->_result) && $default !== null) {
            return $default;
        }

        return $this->_result[$key];
    }

    public function getCapacityUnitsConsumed()
    {
        return $this->param('ConsumedCapacity');
    }

    /**
     * Provide the result count returned by DyanmoDb
     */ 
    public function getReturnCount()
    {
        return $this->param('Count');
    }

    /**
     * Provide the count of items scanned before any filters were applied 
     * as returned by DyanmoDb
     */ 
    public function getScannedCount()
    {
        return $this->param('ScannedCount');
    }

    /**
     * The last key reported by DyanmoDb. Important for pagination
     */ 
    public function getLastEvaluatedKey()
    {
        return $this->param('LastEvaluatedKey', false);
    }
}
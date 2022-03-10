<?php

namespace Bego\Component;

use Bego\Item;

class Resultset implements \Iterator, \Countable
{
    protected $_pointer;

    protected $_marshaler;

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
        return $this->item($this->count() - 1);
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

    /**
     * Instantiate one item from the set
     */
    public function item($index)
    {
        if (!array_key_exists($index, $this->_result['Items'])) {
            return null;
        }

        return new Item(
            $this->_marshaler->unmarshalItem($this->_result['Items'][$index])
        );
    }

    /**
     * Extracts one attribute from every item in the set
     */
    public function attribute($key)
    {
        $collection = [];

        for ($i = 0; $i < $this->count(); $i++) {
            $collection[] = $this->item($i)->attribute($key);
        }

        return $collection;
    }

    /**
     * Perform aggregated sum on one attribute for 
     * every item in the set
     */
    public function sum($key)
    {
        return array_sum($this->attribute($key));
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
     * Return the number of trips required by the paginator
     */
    public function getQueryCount()
    {
        return $this->param('X-Query-Count');
    }

    /** 
     * Return the execution time of the query(ies)
     */
    public function getQueryTime()
    {
        return $this->param('X-Query-Time');
    }

    /**
     * The last key reported by DyanmoDb. Important for pagination
     */ 
    public function getLastEvaluatedKey()
    {
        return $this->param('LastEvaluatedKey', false);
    }

    /**
     * Return the items as an array
     */
    public function toArray()
    {
        return $this->_result['Items'];
    }
}
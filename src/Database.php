<?php

namespace Bego;

class Database
{
    protected $_client;

    protected $_marshaler;

    public function __construct($client, $marshaler)
    {
        $this->_client = $client;
        $this->_marshaler = $marshaler;
    }

    public function client()
    {
        return $this->_client;
    }

    public function marshaler()
    {
        return $this->_marshaler;
    }

    public function table($model, $consumption = false)
    {
        return new Table($this, $model, $consumption);
    }
}
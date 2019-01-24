<?php

namespace Bego\Create;

class Statement
{
    protected $_options = [
        'TableName'         => null,
        'ProvisionedThroughput' => array(
            'ReadCapacityUnits'  => 5,
            'WriteCapacityUnits' => 5,
        ),
    ];

    public function set($key, $value)
    {
        $this->_options[$key] = $value;

        return $this;
    }

    public function append($key, $value)
    {
        if (!isset($key, $this->_options)) {
            $this->_options[$key] = [];
        }

        array_push($this->_options[$key], $value);

        return $this;
    }

    public function table()
    {
        return $this->set('TableName', $value);
    }

    public function provision($read, $write)
    {
        return $this->set('ProvisionedThroughput', [
            'ReadCapacityUnits'  => (int) $read,
            'WriteCapacityUnits' => (int) $write
        ]);
    }

    public function attribute($name, $type)
    {
        return $this->append('AttributeDefinitions', [
            'AttributeName' => $name,
            'AttributeType' => $type, // S | N | B
        ]);
    }

    public function key($name, $type)
    {
        return $this->append('KeySchema', [
            'AttributeName' => $name,
            'KeyType' => $type, // HASH | RANGE
        ]);
    }

    public function compile()
    {
        return $this->_options;
    }
}
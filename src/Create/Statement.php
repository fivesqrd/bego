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

        if (!is_array($this->_options[$key])) {
            $this->_options[$key] = [];
        }

        array_push($this->_options[$key], $value);

        return $this;
    }

    public function table($value)
    {
        return $this->set('TableName', $value);
    }

    public function provision($read, $write)
    {
        return $this->set(
            'ProvisionedThroughput', $this->_getProvisionedThroughput($read, $write) 
        );
    
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
        return $this->append(
            'KeySchema', $this->_getKeySchema($name, $type)
        );
    }

    protected function _getKeySchema($name, $type)
    {
        return [
            'AttributeName' => $name,
            'KeyType' => $type, // HASH | RANGE
        ];
    }

    protected function _getProvisionedThroughput($read, $write)
    {
        return [
            'ReadCapacityUnits'  => (int) $read,
            'WriteCapacityUnits' => (int) $write
        ];
    }

    public function global($name, $keys, $capacity)
    {

        foreach ($keys as $key) {
            $schema[] = $this->_getKeySchema($key['name'], $key['type']);
        }

        return $this->append('GlobalSecondaryIndexes', [ 
            'IndexName'  => $name,
            'KeySchema'  => $schema,
            'Projection' => [ 
               //'NonKeyAttributes' => [],
               'ProjectionType' => 'all' 
            ],
            'ProvisionedThroughput' => $this->_getProvisionedThroughput(
                $capacity['read'], $capacity['write']
            ), 
        ]);
    }

    public function compile()
    {
        print_r($this->_options);
        return $this->_options;
    }
}

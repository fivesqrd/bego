<?php

namespace Bego\Query;

class Statement
{
    protected $_options;

    protected $_client;

    protected $_marshaler;

    protected $_trips = 0;

    protected $_pointer;

    public function __construct($client, $marshaler, $options)
    {
        $this->_client = $client;
        $this->_marshaler = $marshaler;
        $this->_options = $options;
    }

    public function fetchAll()
    {
        $options = $this->_options;
        $collection = array();
        $result = array();
        $i = 0;

        while ($i == 0 || isset($result['LastEvaluatedKey'])) {
            $result = $this->_client->query($options);
            $options['ExclusiveStartKey'] = $result['LastEvaluatedKey'];

            foreach ($result['Items'] as $item) {
                $collection[] = $this->_marshaler->unmarshalItem($item);
            }

            $i++;
            $this->_pointer = $result['LastEvaluatedKey'];
        }

        $this->_trips = $i;

        return $collection;
    }

    public function getLastTripCount()
    {
        return $this->_trips;
    }

    public function getLastEvaluatedKey()
    {
        return $this->_pointer;
    }

    public function fetchMany($limit)
    {
        $options = $this->_options;
        $collection = array();
        $result = array();

        while (count($collection) < $limit && isset($result['LastEvaluatedKey'])) {
            $result = $this->_client->query($options);
            $options['ExclusiveStartKey'] = $result['LastEvaluatedKey'];

            foreach ($result['Items'] as $item) {
                $collection[] = $this->_marshaler->unmarshalItem($item);
            }

            $i++;
            $this->_pointer = $result['LastEvaluatedKey'];
        }

        $this->_trips = $i;

        return $collection;
    }

    public function fetch()
    {
        $result = $this->_client->query($this->_options);

        $collection = array();

        foreach ($result['Items'] as $item) {
            $collection[] = $this->_marshaler->unmarshalItem($item);
        }

        $this->_trips = 1;
        $this->_pointer = $result['LastEvaluatedKey'];

        return $collection;
    }
}
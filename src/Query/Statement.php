<?php

namespace Bego\Query;

class Statement
{
    protected $_options;

    protected $_client;

    public function __construct($cliemt, $options)
    {
        $this->_client = $client;
        $this->_options = $options;
    }

    public function fetchAll()
    {
        $options = $this->_options;
        $collection = array();
        $result = array();

        while (isset($result['LastEvaluatedKey'])) {
            $result = $this->_client->query($options);
            $options['ExclusiveStartKey'] = $result['LastEvaluatedKey'];

            foreach ($result['Items'] as $item) {
                $collection[] = $marshaler->unmarshalItem($item);
            }
        }

        return $collection;
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
                $collection[] = $marshaler->unmarshalItem($item);
            }
        }

        return $collection;
    }

    public function page()
    {
        $result = $this->_client->query($this->_options);

        $collection = array();

        foreach ($result['Items'] as $item) {
            $collection[] = $marshaler->unmarshalItem($item);
        }

        return $collection;
    }
}
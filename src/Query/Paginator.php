<?php

namespace Bego\Query;

class Paginator
{
    protected $_db;

    protected $_options = [];

    protected $_trips = 0;

    protected $_key = false;

    protected $_result = [
        'Items'             => [],
        'Count'             => null,
        'ScannedCount'      => null,
        'ConsumedCapacity'  => null,
        'LastEvaluatedKey'  => null
    ];

    public function __construct($db, $options, $key = null)
    {
        $this->_db= $db;
        $this->_options = $options;
        $this->_key = $key;
    }

    protected function _execute($options, $offset, $trips, $limit = null)
    {
        if ($limit && $trips >= $limit) {
            return false;
        }

        if ($offset === null) {
            return false;
        }

        if ($offset) {
            $options['ExclusiveStartKey'] = $offset;
        }

        return $this->_db->client()->query($options);
    }

    public function query($limit = null)
    {
        $trips = 0;
        $key = $this->_key;

        do {
            $result = $this->_execute(
                $this->_options, $key, $trips, $limit
            );

            $this->_aggregate($result);

            $trips += 1;

            if (isset($result['LastEvaluatedKey'])) {
                $key = $result['LastEvaluatedKey'];
            }

        } while ($result !== false);

        $this->_trips += $trips;

        return $this->_result;
    }

    public function getTripCount()
    {
        return $this->_trips;
    }

    protected function _aggregate($result)
    {
        if (isset($result['Items'])) {
            $this->_result['Items'] = array_merge(
                $this->_result['Items'], $result['Items']
            );
        }

        if (isset($result['Count'])) {
            $this->_result['Count'] += $result['Count'];
        }

        if (isset($result['ScannedCount'])) {
            $this->_result['ScannedCount'] += $result['ScannedCount'];
        }

        if (isset($result['ConsumedCapacity'])) {
            $this->_result['ConsumedCapacity'] += $result['ConsumedCapacity'];
        }

        if (isset($result['LastEvaluatedKey'])) {
            $this->_result['LastEvaluatedKey'] = $result['LastEvaluatedKey'];
        } else {
            $this->_result['LastEvaluatedKey'] = null;
        }
    }
}
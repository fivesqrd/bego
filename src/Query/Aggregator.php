<?php

namespace Bego\Query;

class Aggregator
{
    protected $_result = [
        'Items'             => [],
        'Count'             => null,
        'ScannedCount'      => null,
        'ConsumedCapacity'  => null,
        'LastEvaluatedKey'  => null
    ];

    public function result()
    {
        return $this->_result;
    }

    public function append($result)
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

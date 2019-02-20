<?php

namespace Bego\Query;

use Bego\Exception as BegoException;
use Bego\Component;
use Bego\Condition;

class Statement
{
    protected $_options = [
        'TableName'         => null,
        'ScanIndexForward'  => true,
    ];

    protected $_partition;

    protected $_filters = [];

    protected $_conditions = [];

    protected $_db;

    public function __construct($db)
    {
        $this->_db = $db;
    }

    public function option($key, $value)
    {
        $this->_options[$key] = $value;

        return $this;
    }

    public function table($value)
    {
        return $this->option('TableName', $value);
    }

    public function index($value)
    {
        return $this->option('IndexName', $value);
    }

    public function consistent($flag = true)
    {
        return $this->option('ConsistentRead', ($flag === true));
    }

    public function reverse($flag = true)
    {
        return $this->option('ScanIndexForward', ($flag !== true));
    }

    public function consumption($flag = true)
    {
        return $this->option('ReturnConsumedCapacity', ($flag === true) ? 'TOTAL' : 'NONE');
    }

    public function limit($value)
    {
        return $this->option('Limit', (int) $value);
    }

    public function offset($value)
    {
        return $this->option('ExclusiveStartKey', $value);
    }

    public function partition($value)
    {
        $this->_partition = $value;

        return $this;
    }

    public function key($value)
    {
        if (!$this->_partition) {
            throw new BegoException('Partition key attribute name not set');
        }

        return $this->condition(
            Condition::comperator($this->_partition, '=', $value)
        );
    }

    public function filter($condition)
    {
        $this->_filters[] = $condition;

        return $this;
    }

    public function condition($condition)
    {
        $this->_conditions[] = $condition;
        
        return $this;
    }

    public function compile()
    {

        if (empty($this->_conditions)) {
            throw new BegoException(
                'A condition expression is required to perform a query'
            );
        }

        $attributes = new AttributeMerge(
            array_merge($this->_filters, $this->_conditions)
        );

        $options = [
            'KeyConditionExpression'    => $this->_createAndExpression($this->_conditions),
            'ExpressionAttributeNames'  => $attributes->names(),
            'ExpressionAttributeValues' => $this->_db->marshaler()->marshalJson(
                json_encode($attributes->values())
            )
        ];

        if (!empty($this->_filters)) {
            $options['FilterExpression'] = $this->_createAndExpression($this->_filters);
        }

        return array_merge($this->_options, $options);
    }

    public function fetch($pages = 1, $offset = null)
    {
        return new Component\Resultset(
            $this->_db->marshaler(), $this->paginator($pages, $offset)->query()
        );
    }

    public function paginator($pages = 1, $offset = false)
    {
        $conduit = new Conduit($this->_db, $this->compile());

        return new Component\Paginator(
            $conduit, $pages, $offset
        );
    }

    protected function _createAndExpression($conditions)
    {
        $statements = [];

        foreach ($conditions as $condition) {
            $statements[] = $condition->statement();
        }

        return implode(' and ', $statements);
    }
}

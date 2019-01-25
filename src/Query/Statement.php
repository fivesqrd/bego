<?php

namespace Bego\Query;

use Bego\Exception as BegoException;

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
        return $this->option('Limit', $value);
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
            throw new \Exception('Partition key attribute name not set');
        }

        return $this->condition($this->_partition, '=', $value);
    }

    public function filter($name, $operator, $value)
    {
        array_push($this->_filters, [
            'name'     => $name,
            'operator' => $operator,
            'value'    => $value
        ]);

        return $this;
    }

    public function condition($name, $operator, $value)
    {
        array_push($this->_conditions, [
            'name'     => $name,
            'operator' => $operator,
            'value'    => $value
        ]);
        
        return $this;
    }

    public function compile()
    {
        $options = [];

        $conditions = new Expression($this->_conditions);
        $filters    = new Expression($this->_filters);

        if (!$conditions->isDirty()) {
            throw new BegoException(
                'A condition expression is required to perform a query'
            );
        }

        $options['KeyConditionExpression'] = $conditions->statement();

        if ($filters->isDirty()) {
            $options['FilterExpression'] = $filters->statement();
        }

        $options['ExpressionAttributeNames'] = array_merge(
            $filters->names(), $conditions->names()
        );

        $values = array_merge(
            $filters->values(), $conditions->values()
        );

        $options['ExpressionAttributeValues'] = $this->_db->marshaler()->marshalJson(
            json_encode($values)
        );

        return array_merge($this->_options, $options);
    }

    public function fetch($pages = 1, $offset = null)
    {
        return new Resultset(
            $this->_db->marshaler(), $this->paginator($pages, $offset)->query()
        );
    }

    public function paginator($pages = 1, $offset = false)
    {
        $conduit = new Conduit($this->_db, $this->compile());

        return new Paginator(
            $conduit, $pages, $offset
        );
    }
}

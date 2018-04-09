<?php

namespace Bego\Query;

class Statement
{
    protected $_options = [
        'TableName'         => null,
        'ScanIndexForward'  => true,
    ];

    protected $_filters = [];

    protected $_conditions = [];

    protected $_client;

    protected $_marshaler;

    public static function create($client, $marshaler)
    {
        return new self($client, $marshaler);
    }

    public function __construct($client, $marshaler)
    {
        $this->_client = $client;
        $this->_marshaler = $marshaler;
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
        return $this->option('ScanIndexForward', ($flag === true));
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

    public function filter($field, $operator, $value)
    {
        array_push($this->_filters, [
            'field'    => $field,
            'operator' => $operator,
            'value'    => $value
        ]);

        return $this;
    }

    public function condition($field, $operator, $value)
    {
        array_push($this->_conditions, [
            'field'    => $field,
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

        if ($conditions->isDirty()) {
            $options['KeyConditionExpression'] = $conditions->statement();
        }

        if ($filters->isDirty()) {
            $options['FilterExpression'] = $filters->statement();
        }

        $options['ExpressionAttributeNames'] = array_merge(
            $filters->names(), $conditions->names()
        );

        $values = array_merge(
            $filters->values(), $conditions->values()
        );

        $options['ExpressionAttributeValues'] = $this->_marshaler->marshalJson(
            json_encode($values)
        );

        return array_merge($this->_options, $options);
    }

    public function fetch($limit = 1, $key = false)
    {
        $paginator = new Paginator(
            $this->_client, $this->compile(), $key
        );

        return new Resultset(
            $this->_marshaler, $paginator->query($limit)
        );
    }
}
<?php

namespace Bego\Query;

class Build
{
    protected $_options = [
        'TableName'         => null,
        'ScanIndexForward'  => true,
    ];

    protected $_filters = [];

    protected $_conditions = [];

    public static function create($client)
    {
        return new self($client);
    }

    public function __construct($client)
    {
        $this->_client = $client;
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

    public function reverse()
    {
        return $this->option('ScanIndexForward', false);
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

    public function options()
    {
        $options = [];

        $conditions = new Query\Expression($this->_conditions);
        $filters    = new Query\Expression($this->_filters);

        if ($conditions->isDirty()) {
            $options['KeyConditionExpression'] = $conditions->statenent();
        }

        if ($filters->isDirty()) {
            $options['FilterExpression'] = $filters->statement();
        }

        $options['ExpressionAttributeNames'] = array_merge(
            $filters->names(), $conditions->names()
        );

        $values = array_merge(
            $filters->names(), $conditions->names()
        );

        $options['ExpressionAttributeValues'] = $marshaler->marshalJson(
            json_encode($values)
        );

        return array_merge($this->_options, $options);
    }

    public function prepare()
    {
        return new Query\Statement($this->_client, $this->options());
    }
}
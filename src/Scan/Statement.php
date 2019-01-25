<?php

namespace Bego\Scan;

use Bego\Exception as BegoException;
use Bego\Component;

class Statement
{
    protected $_options = [
        'TableName'         => null,
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

    public function filter($name, $operator, $value)
    {
        array_push($this->_filters, [
            'name'     => $name,
            'operator' => $operator,
            'value'    => $value
        ]);

        return $this;
    }

    public function compile()
    {
        $options = [];

        $filters    = new Component\Expression($this->_filters);

        if ($filters->isDirty()) {
            $options['FilterExpression'] = $filters->statement();

            $options['ExpressionAttributeNames'] = $filters->names();

            $options['ExpressionAttributeValues'] = $this->_db->marshaler()->marshalJson(
                json_encode($filters->values())
            );
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
}

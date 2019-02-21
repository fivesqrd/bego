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

    public function filter($condition)
    {
        $this->_filters[] = $condition;

        return $this;
    }

    public function compile()
    {
        $options = [];

        if (empty($this->_filters)) {
            return $this->_options;
        }

        $attributes = new AttributeMerge($this->_filters);

        $options = [
            'FilterExpression'          => $this->_createAndExpression($this->_filters),
            'ExpressionAttributeNames'  => $attributes->names(),
        ];

        if (count($attributes->values()) > 0) {
            $option['ExpressionAttributeValues'] = $this->_db->marshaler()->marshalJson(
                json_encode($attributes->values())
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

    protected function _createAndExpression($conditions)
    {
        $statements = [];

        foreach ($conditions as $condition) {
            $statements[] = $condition->statement();
        }

        return implode(' and ', $statements);
    }
}

<?php

namespace Bego\Query;

use Bego\Exception as BegoException;
use Bego\Component\Member;
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

    protected $_projection = [];

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

    /**
     * Accept an array of attribute names in ProjectionExpression
     * @param array $attributeNames
     * @return Statement
     */
    public function projection($values)
    {
        if (is_string($values)) {
            $values = [$values];
        }

        if (!is_array($values)) {
            throw new BegoException(
                'Projection values are expected to by of type array'
            );
        }

        foreach ($values as $value) {
            $this->_projection[] = new Component\AttributeName($value);
        }

        return $this;
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
                'A key condition expression is required to perform a query'
            );
        }

        $expressions = [
            new Member\KeyConditionExpression($this->_conditions),
            new Member\FilterExpression($this->_filters),
            new Member\ProjectionExpression($this->_projection),
        ];

        $attributes = [
            new Member\AttributeNames($expressions),
            new Member\AttributeValues($this->_db->marshaler(), $expressions)
        ];

        foreach (array_merge($expressions, $attributes) as $member) {
            
            if (!$member->isDefined()) {
                continue;
            }
            
            $this->option($member->getParameterKey(), $member->statement()); 
        }

        return $this->_options;
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

<?php

namespace Bego\Update;

use Bego\Exception as BegoException;
use Bego\Component;

class Statement
{
    protected $_options = [
        'TableName'                 => null,
        'Key'                       => null,
        'ExpressionAttributeNames'  => [],
        'ExpressionAttributeValues' => [],
        'UpdateExpression'          => null,
    ];

    protected $_table;

    protected $_actions;

    protected $_conditions = [];

    protected $_marshaler;

    public function __construct($table, $marshaler, $actions)
    {
        $this->_marshaler = $marshaler;
        $this->_table = $table;
        $this->_actions = $actions;
    }

    public function isDirty()
    {
        return $this->_actions->isDirty();
    }

    public function option($key, $value)
    {
        $this->_options[$key] = $value;

        return $this;
    }

    public function key($value)
    {
        $this->_options['Key'] = $value;
    }

    public function conditions($conditions)
    {
        $this->_conditions = $conditions;
    }

    public function compile()
    {
        if (!$this->isDirty()) {
            throw new BegoException('No update expression values for item');
        }

        $options = [
            'TableName'                 => $this->_table,
            'UpdateExpression'          => $this->_actions->statement(),
            'ExpressionAttributeNames'  => $this->_getAttributeNames(),
            'ExpressionAttributeValues' => $this->_marshaler->marshalJson(
                json_encode($this->_getAttributeValues())
            )
        ];

        if (count($this->_conditions) > 0) {
            $options['ConditionExpression'] = $this->_getConditionExpression();
        }

        return array_merge($this->_options, $options);
    }

    protected function _getAttributeNames()
    {
        $names = $this->_actions->names();

        foreach ($this->_conditions as $condition) {
            $names = array_merge($names, $condition->name());
        }

        return $names;
    }

    protected function _getAttributeValues()
    {
        $values = $this->_actions->values();

        foreach ($this->_conditions as $condition) {
            $values = array_merge($values, $condition->values());
        }

        return $values;
    }

    protected function _getConditionExpression()
    {
        $statements = [];

        foreach ($this->_conditions as $condition) {
            $statements[] = $condition->statement();
        }

        return implode(' and ', $statements);
    }
}
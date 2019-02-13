<?php

namespace Bego\Update;


use Aws\DynamoDb\Exception\DynamoDbException;
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

    public function __construct($table, $actions)
    {
        $this->_table = $table;
        $this->_actions = $actions;
    }

    public function option($key, $value)
    {
        $this->_options[$key] = $value;

        return $this;
    }

    public function key($value)
    {
        $this->_options['Key'] = $value;

        return $this;
    }

    public function conditions($conditions)
    {
        $this->_conditions = $conditions;

        return $this;
    }

    public function compile($marshaler)
    {
        if (!$this->_actions->isDirty()) {
            throw new BegoException('No update expression values for item');
        }

        $attributes = new AttributeMerge(
            $this->_actions, $this->_conditions
        );

        $options = [
            'TableName'                 => $this->_table,
            'UpdateExpression'          => $this->_actions->expression(),
            'ExpressionAttributeNames'  => $attributes->names(),
            'ExpressionAttributeValues' => $marshaler->marshalJson(
                json_encode($attributes->values())
            )
        ];

        if (count($this->_conditions) > 0) {
            $options['ConditionExpression'] = $this->_getConditionExpression();
        }

        return array_merge($this->_options, $options);
    }

    public function execute($client, $marshaler)
    {
        try {

            /* If nothing changed, do nothing */
            if (!$this->_actions->isDirty()) {
                return null;
            }

            $result = $client->updateItem(
                $this->compile($marshaler)
            );

            $response = $result->get('@metadata');

            if ($response['statusCode'] != 200) {
                throw new Exception(
                    "DynamoDb returned unsuccessful response code: {$response['statusCode']}"
                );
            }

            /* Mark item is clean */
            $this->_actions->clean();

            return true;

        } catch (DynamoDbException $e) {

            if ($e->getAwsErrorCode() == 'ConditionalCheckFailedException') {
                return false;
            }

            throw $e;
        }
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
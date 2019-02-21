<?php

namespace Bego\Put;


use Aws\DynamoDb\Exception\DynamoDbException;
use Bego\Exception as BegoException;
use Bego\Component;

class Statement
{
    protected $_options = [
        'TableName'     => null,
        'Item'          => null,
    ];

    protected $_table;

    protected $_attributes;

    protected $_conditions = [];

    public function __construct($table, $attributes)
    {
        $this->_table = $table;
        $this->_attributes = $attributes;
    }

    public function conditions($conditions)
    {
        $this->_conditions = $conditions;

        return $this;
    }

    public function compile($marshaler)
    {
        $options = [
            'TableName' => $this->_table,
            'Item'      => $marshaler->marshalJson(
                json_encode($this->_attributes)
            ),
        ];

        if (count($this->_conditions) > 0) {
            $attributes = new AttributeMerge($this->_conditions);

            $options['ExpressionAttributeNames'] = $attributes->names();
            $options['ConditionExpression'] = $this->_getAndExpression(
                $this->_conditions
            );
        }

        return array_merge($this->_options, $options);
    }

    public function execute($client, $marshaler)
    {
        try {

            $result = $client->putItem(
                $this->compile($marshaler)
            );

            $response = $result->get('@metadata');

            if ($response['statusCode'] != 200) {
                throw new Exception(
                    "DynamoDb returned unsuccessful response code: {$response['statusCode']}"
                );
            }

            return true;

        } catch (DynamoDbException $e) {

            if ($e->getAwsErrorCode() == 'ConditionalCheckFailedException') {
                return false;
            }

            throw $e;
        }
    }

    protected function _getAndExpression($conditions)
    {
        $statements = [];

        foreach ($conditions as $condition) {
            $statements[] = $condition->statement();
        }

        return implode(' and ', $statements);
    }
}
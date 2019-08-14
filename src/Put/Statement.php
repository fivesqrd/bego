<?php

namespace Bego\Put;


use Aws\DynamoDb\Exception\DynamoDbException;
use Bego\Exception as BegoException;
use Bego\Component\Member;
use Bego\Component;

class Statement
{
    protected $_options = [
        'TableName'     => null,
        'Item'          => null,
    ];

    protected $_db;

    protected $_table;

    protected $_attributes;

    protected $_conditions = [];

    public function __construct($db, $attributes)
    {
        $this->_db = $db;
        $this->_attributes = $attributes;
    }

    public function table($value)
    {
        $this->_options['TableName'] = $value;
        return $this;
    }

    public function conditions($conditions)
    {
        $this->_conditions = $conditions;

        return $this;
    }

    public function compile()
    {
        $this->_options['Item'] = $this->_db->marshaler()->marshalJson(
            json_encode($this->_attributes)
        );

        $expressions = [
            new Member\ConditionExpression($this->_conditions),
        ];

        $attributes = [
            new Member\AttributeNames($expressions),
            new Member\AttributeValues($this->_db->marshaler(), $expressions)
        ];

        foreach (array_merge($expressions, $attributes) as $member) {
            
            if (!$member->isDefined()) {
                continue;
            }

            $this->_options[$member->getParameterKey()] = $member->statement(); 
        }

        return $this->_options;
    }

    public function execute()
    {
        try {

            $result = $this->_db->client()->putItem($this->compile());

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
}
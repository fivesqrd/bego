<?php

namespace Bego\Update;


use Aws\DynamoDb\Exception\DynamoDbException;
use Bego\Exception as BegoException;
use Bego\Component\Member;

class Statement
{
    protected $_options = [
        'TableName'                 => null,
        'Key'                       => null,
        'ExpressionAttributeNames'  => [],
        'ExpressionAttributeValues' => [],
        'UpdateExpression'          => null,
    ];

    protected $_db;

    protected $_item;

    protected $_conditions = [];

    public function __construct($db, $item)
    {
        $this->_db = $db;
        $this->_item = $item;
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

    public function key($value)
    {
        return $this->option('Key', $value);
    }

    public function conditions($conditions)
    {
        $this->_conditions = $conditions;

        return $this;
    }

    public function compile()
    {
        if (!$this->_item->isDirty()) {
            throw new BegoException('No update expression values for item');
        }

        $expressions = [
            new Member\UpdateExpression($this->_item),
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
            
            $this->option($member->getParameterKey(), $member->statement()); 
        }

        return $this->_options;
    }

    public function execute()
    {
        try {

            /* If nothing changed, do nothing */
            if (!$this->_item->isDirty()) {
                return null;
            }

            $result = $this->_db->client()->updateItem($this->compile());

            $response = $result->get('@metadata');

            if ($response['statusCode'] != 200) {
                throw new Exception(
                    "DynamoDb returned unsuccessful response code: {$response['statusCode']}"
                );
            }

            /* Mark item is clean */
            $this->_item->clean();

            return true;

        } catch (DynamoDbException $e) {

            if ($e->getAwsErrorCode() == 'ConditionalCheckFailedException') {
                return false;
            }

            throw $e;
        }
    }
}
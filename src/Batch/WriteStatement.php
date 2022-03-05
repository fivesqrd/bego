<?php

namespace Bego\Batch;


use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Result;
use Bego\Exception as BegoException;
use Bego\Component\Member;
use Bego\Component;

class WriteStatement
{
    protected $_options = [
        'RequestItems'  => [],
        'ReturnConsumedCapacity' => 'NONE',
        'ReturnItemCollectionMetrics' => 'SIZE'
    ];

    protected $_puts = [];

    protected $_deletes = [];

    protected $_db;

    public function __construct($db)
    {
        $this->_db = $db;
    }

    /**
     * Add a delete request to the batch
     */
    public function delete($table, $key)
    {
        if (!array_key_exists($table, $this->_deletes)) {
            $this->_deletes[$table] = [];
        }

        $this->_deletes[$table][] = $key;
        return $this;
    }

    /**
     * Add a put request to the batch
     */
    public function put($table, $attributes)
    {
        if (!array_key_exists($table, $this->_puts)) {
            $this->_puts[$table] = [];
        }

        $this->_puts[$table][] = $attributes;
        return $this;
    }

    protected function _addRequest($options, $table, $request, $attributes)
    {
        if (!isset($options['RequestItems'][$table])) {
            $options['RequestItems'][$table] = [];
        }

        $options['RequestItems'][$table][] = [$request => $attributes];

        return $options;
    }

    public function compile()
    {
        $options = array_merge($this->_options, ['RequestItems' => []]);

        foreach ($this->_deletes as $table => $keys) {
            if (!isset($options['RequestItems'][$table])) {
                $options['RequestItems'][$table] = [];
            }

            foreach ($keys as $key) {
                $options['RequestItems'][$table][] = [
                    'DeleteRequest' => ['Key' => $key]
                ];
            }
        }

        foreach ($this->_puts as $table => $items) {
            if (!isset($options['RequestItems'][$table])) {
                $options['RequestItems'][$table] = [];
            }

            foreach ($items as $item) {
                $options['RequestItems'][$table][] = [
                    'PutRequest' => ['Item' => $this->_db->marshaler()->marshalJson(json_encode($item))]
                ];
            }
        }

        return $options;
    }

    public function execute($tries = 1)
    {
        $result = $this->_tryExecute($this->compile());

        $unprocessed = $result->get('UnprocessedItems');

        // If we have retries enabled and unprocessed items
        if ($tries > 1 && !empty($unprocessed)) {
            $result = $this->retry($result, $tries - 1);
        }

        return $result;
    }

    public function retry(Result $result, $tries)
    {
        do {
            $i = 1;

            // Back off before retrying
            sleep($i * 2);

            $result = $this->_tryExecute($result->get('UnprocessedItems'));

            $unprocessed = $result->get('UnprocessedItems');

            if ($i == $tries) {
                throw new BegoException(
                    "DynamoDb BatchWrite returned " . count($unprocessed) . " unprocessed items after {$i} tries"
                );
            } 
            
            $i++;
        } while (!empty($unprocessed));

        return $result;
    }

    protected function _tryExecute($statement)
    {
        try {

            $result = $this->_db->client()->batchWriteItem($statement);

            $response = $result->get('@metadata');

            if ($response['statusCode'] != 200) {
                throw new BegoException(
                    "DynamoDb returned unsuccessful response code: {$response['statusCode']}"
                );
            }

            return $result;

        } catch (DynamoDbException $e) {

            if ($e->getAwsErrorCode() == 'ConditionalCheckFailedException') {
                return false;
            }

            throw $e;
        }
    }
}
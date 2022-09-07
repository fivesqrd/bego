<?php

namespace Bego\Batch;

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\WriteRequestBatch;

/**
 * Wraps the AWS WriteRequestBatch class which takes care of 
 * - batch limits, 
 * - concurrency,
 * - unresolved items and retries
 */
class WriteWrapper
{
    protected $_db;

    protected $_batch;

    public static function make($db, $table, $poolSize = 3, $batchSize = 25)
    {
        $batch = new WriteRequestBatch($db->client(), [
            'table' => $table, 
            'pool_size' => $poolSize,
            'autoflush' => true,
            'batch_size' => $batchSize,
            'error' => function (DynamoDbException $e) { throw $e; }
        ]);

        return new static($db, $batch);
    }

    public function __construct($db, $batch)
    {
        $this->_db = $db;
        $this->_batch = $batch;
    }

    public function deleteMany(array $keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return $this;
    }

    public function putMany($items)
    {
        foreach ($items as $item) {
            $this->put($item);
        }
        return $this;
    }

    /**
     * Add a put request to the batch
     */
    public function put($itemAttributes)
    {
        $this->_batch->put(
            $this->_db->marshaler()->marshalJson(json_encode($itemAttributes))
        );
        return $this;
    }

    /**
     * Add a delete request to the batch
     */
    public function delete($key)
    {
        $this->_batch->delete($key);
        return $this;
    }

    public function flush()
    {
        return $this->_batch->flush(true);
    }
}
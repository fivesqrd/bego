<?php

namespace Bego;

use Bego\Component\Resultset;

class Table
{
    protected $_db;

    protected $_model;

    public function __construct(Database $db, Model $model)
    {
        $this->_db = $db;
        $this->_model = $model;
    }

    public function create($spec)
    {
        $result = $this->_db->client()->createTable(
            Create\Factory::model($this->_model, $spec)->compile()
        );

        $this->_isResponseValid(
            $result->get('@metadata'), 200
        );
    }

    public function fetch($partition, $sort = null, $consistent = false)
    {
        $result = $this->_db->client()->getItem([
            'TableName'     => $this->_model->name(),
            'Key'           => $this->_getKey($this->_model, $partition, $sort),
            'ConsistenRead' => $consistent
        ]);

        return new Item(
            $result->hasKey('Item') 
                ? $this->_db->marshaler()->unmarshalItem($result->get('Item'))
                : []
        );
    }

    public function put($attributes, $conditions = [])
    {
        $result = (new Put\Statement($this->_db, $attributes))
            ->table($this->_model->name())
            ->conditions($conditions)
            ->execute();

        if (!$result) {
            return false;
        }

        return new Item($attributes);
    }

    public function update($item, $conditions = [])
    {
        return (new Update\Statement($this->_db, $item))
            ->table($this->_model->name())
            ->key($this->_getKeyFromItem($this->_model, $item))
            ->conditions($conditions)
            ->execute();
    }

    public function delete($item)
    {
        /* Generate a key from the table model */
        $key = $this->_getKeyFromItem($this->_model, $item);

        $result = $this->_db->client()->deleteItem([
            'TableName' => $this->_model->name(),
            'Key'       => $key
        ]);

        return $this->_isResponseValid(
            $result->get('@metadata'), 200
        );
    }

    /**
     * Perform a batch put write. If you are sending batches of large
     * items, you may consider lowering the batch size, otherwise, you should use 25.
     * @param array $items
     * @param int $workers
     * @param int $limit
     * @return bool
     * @throws \Aws\DynamoDb\Exception\DynamoDbException
     */
    public function putBatch(array $items, $workers = 1, $limit = 25)
    {
        $wrapper = Batch\WriteWrapper::make(
            $this->_db, $this->_model->name(), $workers, $limit
        );
        
        $wrapper->putMany($items);
        $wrapper->flush();

        return true;
    }

    /**
     * Perform a batch delete write.
     * @param array $items
     * @param int $workers
     * @param int $limit
     * @return bool
     * @throws \Aws\DynamoDb\Exception\DynamoDbException
     */
    public function deleteBatch(array $items, $workers = 1, $limit = 25)
    {
        $wrapper = Batch\WriteWrapper::make(
            $this->_db, $this->_model->name(), $workers, $limit
        );
        
        foreach ($items as $item) {
            $wrapper->delete($this->_getKeyFromItem($this->_model, $item));
        }

        $wrapper->flush();

        return true;
    }

    public function deleteResultset(Resultset $resultset)
    {
        $this->deleteBatch($resultset->toArrayOfObjects());
    }

    public function query($index = null)
    {
        return $index === null 
            ? Query\Factory::table($this->_db, $this->_model)
            : Query\Factory::index($this->_db, $this->_model, $index);
    }

    public function scan($index = null)
    {
        return $index === null 
            ? Scan\Factory::table($this->_db, $this->_model)
            : Scan\Factory::index($this->_db, $this->_model, $index);
    }

    protected function _getKeyFromItem($model, $item)
    {
        return $this->_getKey(
            $model, $item->attribute($model->partition()), $item->attribute($model->sort())
        );
    }

    protected function _getKey($model, $partition, $sort = null)
    {
        $keys[$model->partition()] = $this->_db->marshaler()->marshalValue($partition);

        if ($sort) {
            $keys[$model->sort()] = $this->_db->marshaler()->marshalValue($sort);
        }

        return $keys;
    }

    protected function _isResponseValid($response, $expected)
    {
        if ($response['statusCode'] == $expected) {
            return true;
        }

        throw new Exception(
            "DynamoDb returned unsuccessful response code: {$response['statusCode']}"
        );
    }
}

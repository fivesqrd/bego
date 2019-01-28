<?php

namespace Bego;

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

        $response = $result->get('@metadata');

        if ($response['statusCode'] != 200) {
            throw new Exception(
                "DynamoDb returned unsuccessful response code: {$response['statusCode']}"
            );
        }
    }

    public function fetch($partition, $sort = null, $consistent = false)
    {
        $result = $this->_db->client()->getItem([
            'TableName'     => $this->_model->name(),
            'Key'           => $this->_getKey($this->_model, $partition, $sort),
            'ConsistenRead' => $consistent
        ]);

        return new Item(
            $this->_db->marshaler()->unmarshalItem($result['Item'])
        );
    }

    public function put($attributes)
    {
        $result = $this->_db->client()->putItem([
            'TableName' => $this->_model->name(),
            'Item'      => $this->_db->marshaler()->marshalItem($attributes)
        ]);

        $response = $result->get('@metadata');

        if ($response['statusCode'] != 200) {
            throw new Exception(
                "DynamoDb returned unsuccessful response code: {$response['statusCode']}"
            );
        }

        return new Item($attributes);
    }

    public function update($item)
    {
        /* Create expression values from the attributes changed in the item */
        $expression = Update\Expression::item($item);

        /* If nothing changed, do nothing */
        if (!$expression->isDirty()) {
            return false;
        }

        /* Generate a key from the table model */
        $key = $this->_getKey(
            $this->_model, 
            $item->attribute($this->_model->partition()), 
            $item->attribute($this->_model->sort())
        );

        /* Marshal the expression values */
        $values = $this->_db->marshaler()->marshalJson(
            json_encode($expression->values())
        );

        $result = $this->_db->client()->updateItem([
            'TableName'                 => $this->_model->name(),
            'Key'                       => $key,
            'ExpressionAttributeNames'  => $expression->names(),
            'ExpressionAttributeValues' => $values,
            'UpdateExpression'          => $expression->statement(),
        ]);

        $response = $result->get('@metadata');

        if ($response['statusCode'] != 200) {
            throw new Exception(
                "DynamoDb returned unsuccessful response code: {$response['statusCode']}"
            );
        }

        /* Mark item is clean */
        $item->clean();
    }

    public function delete($item)
    {
        /* Generate a key from the table model */
        $key = $this->_getKey(
            $this->_model, 
            $item->attribute($this->_model->partition()), 
            $item->attribute($this->_model->sort())
        );

        $result = $this->_db->client()->putItem([
            'TableName' => $this->_model->name(),
            'Key'       => $key
        ]);

        $response = $result->get('@metadata');

        if ($response['statusCode'] != 200) {
            throw new Exception(
                "DynamoDb returned unsuccessful response code: {$response['statusCode']}"
            );
        }

        return true;
    }

    public function query($index = null)
    {
        $query = new Query\Statement($this->_db);

        $query->table($this->_model->name());

        if ($index) {
            $spec = $this->_model->index($index);
            $query->index($index);
        }

        return $query->partition(
            isset($spec['key']) ? $spec['key'] : $this->_model->partition()
        );
    }

    public function scan($index = null)
    {
        $scan = new Scan\Statement($this->_db);

        $scan->table($this->_model->name());

        if ($index) {
            $spec = $this->_model->index($index);
            $scan->index($index);
        }

        return $scan;
    }

    protected function _getKey($model, $partition, $sort = null)
    {
        $keys[$model->partition()] = $this->_db->marshaler()->marshalValue($partition);

        if ($sort) {
            $keys[$model->sort()] = $this->_db->marshaler()->marshalValue($sort);
        }

        return $keys;
    }
}

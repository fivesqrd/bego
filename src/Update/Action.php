<?php

namespace Bego\Update;

use Bego\Component\AttributeName;
use Bego\Item;
use Bego\Exception;

/**
 * key condition expression
 */
class Action
{
    protected $_item;

    public function __construct(Item $item)
    {
        $this->_item = $item;
    }

    public function clean()
    {
        $this->_item->clean();
    }

    public function isDirty()
    {
        return $this->_item->isDirty();
    }

    protected function _getRemoveStatement()
    {
        $statements = [];

        foreach ($this->_item->diff() as $diff) {

            if ($diff['action'] != 'remove') {
                continue;
            }

            $attr = new AttributeName($diff['attribute']);

            $statements[] = $attr->key();
        }

        if (count($statements) == 0) {
            return null;
        }

        return 'REMOVE ' . implode(', ', $statements);
    }

    protected function _getSetStatement()
    {
        $statements = [];

        foreach ($this->_item->diff() as $diff) {

            if ($diff['action'] != 'set') {
                continue;
            }

            $attr = new AttributeName($diff['attribute']);

            $statements[] = "{$attr->key()} = {$attr->placeholder()}";
        }

        if (count($statements) == 0) {
            return null;
        }

        return 'SET ' . implode(', ', $statements);
    }

    protected function _getAddStatement()
    {
        $statements = [];

        foreach ($this->_item->diff() as $diff) {

            if ($diff['action'] != 'add') {
                continue;
            }

            $attr = new AttributeName($diff['attribute']);

            $statements[] = "{$attr->key()} {$attr->placeholder()}";
        }

        if (count($statements) == 0) {
            return null;
        }

        return 'ADD ' . implode(', ', $statements);
    }

    protected function _getDeleteStatement()
    {
        $statements = [];

        foreach ($this->_item->diff() as $diff) {

            if ($diff['action'] != 'delete') {
                continue;
            }

            $attr = new AttributeName($diff['attribute']);

            $statements[] = "{$attr->key()} {$attr->placeholder()}";
        }

        if (count($statements) == 0) {
            return null;
        }

        return 'DELETE ' . implode(', ', $statements);
    }

    public function attributeNames()
    {
        $names = [];

        foreach ($this->_item->diff() as $diff) {

            $attr = new AttributeName($diff['attribute']);

            $names[$attr->key()] = $attr->raw();
        }

        return $names;
    }

    public function attributeValues()
    {
        $values = [];

        foreach ($this->_item->diff() as $diff) {

            if ($diff['action'] == 'remove') {
                continue;
            }

            $attr = new AttributeName($diff['attribute']);

            $values[$attr->placeholder()] = $diff['value'];
        }

        return $values;
    }

    public function expression()
    {
        $actions = array_filter([
            $this->_getSetStatement(), $this->_getRemoveStatement(), $this->_getAddStatement(), $this->_getDeleteStatement()
        ]);

        if (count($actions) == 0) {
            throw new Exception('No update actions to perform on this item');
        } 

        return implode(' ', $actions);
    }
}

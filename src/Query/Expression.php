<?php

namespace Bego\Database\Query;

/**
 * key condition expression
 */
class Expression
{
    protected $_names = [];

    protected $_statements = [];

    protected $_values = [];

    public function __construct($items = [])
    {
        foreach ($items as $item) {
            $this->add(
                $item['field'], $item['operator'], $item['value']
            );
        }
    }

    public function add($field, $operator, $value)
    {
        $name = '#' . $field;
        $placeholder = ':' . $field;

        array_push($this->_names, [$name => $field]);

        array_push($this->_statements, "{$name} {$operator} {$placeholder}");

        array_push($this->_values, [$placeholder => $value]);

        return $this;
    }

    public function values()
    {
        return [
            ':n'    => $script->getName(),
            ':t'    => date('Y-m-d H:i:s', $time)
        ];
    }

    public function names()
    {
        return ['#Name' => 'Name', '#Timestamp' => 'Timestamp'];
    }

    public function statement()
    {
        //return implode(' and ', $this->_expression);
        return  '#Name = :n and #Timestamp >= :t';
    }
}
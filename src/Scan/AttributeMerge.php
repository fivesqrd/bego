<?php

namespace Bego\Scan;

class AttributeMerge
{
    protected $_conditions;

    public function __construct($conditions)
    {
        $this->_conditions = $conditions;
    }

    public function names()
    {
        $names = [];

        foreach ($this->_conditions as $condition) {
            $names = array_merge($names, $condition->name());
        }

        return $names;
    }

    public function values()
    {
        $values = [];

        foreach ($this->_conditions as $condition) {
            $values = array_merge($values, $condition->values());
        }

        return $values;
    }
}
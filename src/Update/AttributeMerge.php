<?php

namespace Bego\Update;

class AttributeMerge
{

    public function __construct($actions, $conditions)
    {
        $this->_actions = $actions;
        $this->_conditions = $conditions;
    }

    public function names()
    {
        $names = $this->_actions->attributeNames();

        foreach ($this->_conditions as $condition) {
            $names = array_merge($names, $condition->name());
        }

        return $names;
    }

    public function values()
    {
        $values = $this->_actions->attributeValues();

        foreach ($this->_conditions as $condition) {
            $values = array_merge($values, $condition->values());
        }

        return $values;
    }
}
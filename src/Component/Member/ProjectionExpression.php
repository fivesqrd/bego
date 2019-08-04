<?php

namespace Bego\Component\Member;

class ProjectionExpression
{
    protected $_attributes = [];

    public function __construct($attributes)
    {
        $this->_attributes = $attributes;
    }

    public function isDefined()
    {
        return count($this->_attributes) > 0;
    }

    public function getParameterKey()
    {
        return 'ProjectionExpression';
    }

    public function statement()
    {
        $parts = [];

        foreach ($this->_attributes as $attribute) {
            $parts[] = $attribute->key();
        }

        return implode(', ', $parts);
    }

    public function names()
    {
        $names = [];

        foreach ($this->_attributes as $attribute) {
            $names[$attribute->key()] = $attribute->raw();
        }

        return $names;
    }

    public function values()
    {
        return [];
    }
}
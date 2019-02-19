<?php

namespace Bego;

use Bego\Component\Condition as CompCondition;
use Bego\Component\AttributeName;

/**
 * key condition expression
 */
class Condition
{
    public static function comperator($attribute, $operator, $value)
    {
        return new CompCondition\Comperator(
            new AttributeName($attribute), $operator, $value
        );
    }

    public static function beginsWith($attribute, $value)
    {
        return new CompCondition\BeginsWith(
            new AttributeName($attribute), $value
        );
    }

    public static function attributeExists($attribute)
    {
        return new CompCondition\AttributeExists(
            new AttributeName($attribute)
        );
    }

    public static function notContains($attribute, $value)
    {
        return new CompCondition\NotContains(
            new AttributeName($attribute), $value
        );
    }

    public static function contains($attribute, $value)
    {
        return new CompCondition\Contains(
            new AttributeName($attribute), $value
        );
    }
}

<?php

namespace Bego;

use Bego\Component\Condition as CompCondition;
use Bego\Component\AttributeName;
use Bego\Component;

/**
 * key condition expression
 */
class Condition
{
    public static function attribute($name)
    {
        return new Component\Condition(
            new AttributeName($name)
        );
    }

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

    public static function attributeNotExists($attribute)
    {
        return new CompCondition\AttributeNotExists(
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

    public static function in($attribute, $values)
    {
        return new CompCondition\In(
            new AttributeName($attribute), $values
        );
    }
}

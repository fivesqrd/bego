<?php

namespace Bego\Component;

class Condition
{
    public static function and($conditions)
    {
        $statements = [];

        foreach ($conditions as $condition) {
            $statements[] = $condition->statement();
        }

        return implode(' and ', $statements);
    }

    public static function or($conditions)
    {
        $statements = [];

        foreach ($conditions as $condition) {
            $statements[] = $condition->statement();
        }

        return implode(' or ', $statements);
    }
}
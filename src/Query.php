<?php

namespace Bego;

class Query
{

    public static function create()
    {
        return new Query\Build();
    }
}
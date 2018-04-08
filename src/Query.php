<?php

namespace Bego;

class Query
{
    public static function create($client, $marshaller)
    {
        return new Query\Build($client, $marshaller);
    }
}
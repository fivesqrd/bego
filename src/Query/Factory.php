<?php

namespace Bego\Query;

class Factory
{
    public static function table($db, $model)
    {
        return (new Statement($db))
            ->table($model->name())
            ->partition($model->partition());
    }

    public static function index($db, $model, $name)
    {
        return (new Statement($db))
            ->table($model->name())
            ->index($name)
            ->partition($model->index($name)['key']);
    }
}

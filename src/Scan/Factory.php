<?php

namespace Bego\Scan;

class Factory
{
    public static function table($db, $model)
    {
        return (new Statement($db))->table($model->name());
    }

    public static function index($db, $model, $name)
    {
        return (new Statement($db))
            ->table($model->name())
            ->index($name);
    }
}

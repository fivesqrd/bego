<?php

namespace Bego\Create;

class Factory
{
    public static function model($model, $spec)
    {
        $statement = (new Statement())
            ->table($model->name())
            ->key($model->partition(), 'HASH')
            ->attribute($model->partition(), $spec['types']['partition']); // 'S'

        if (isset($spec['capacity'])) {
            $statement->provision(
                $spec['capacity']['read'], $schema['capacity']['write']
            );
        }

        if ($model->sort()) {
            $statement->key($model->sort(), 'RANGE')
                ->attribute($model->sort(), $spec['types']['sort']);
        }

        foreach ($spec['indexes'] as $name => $index) {
            if ($index['type'] == 'global') {
                $statement->global($name, $index['keys'], $index['capacity']);
            }

            if ($index['type'] == 'local') {
                $statement->local($name, $index['keys']);
            }
        }

        return $statement;
    }
}
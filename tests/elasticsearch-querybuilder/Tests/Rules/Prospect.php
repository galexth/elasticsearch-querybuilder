<?php

namespace Galexth\QueryBuilder\Tests\Rules;

use Galexth\Builder\Rule;

class Prospect implements Rule
{
    public static function patterns(): array
    {
        return [
            'name' => [
                'query_type' => 'match',
                'fields' => 'name',
                'type' => 'text',
            ]
        ];
    }
}
<?php

namespace Galexth\QueryBuilder\Tests\Rules;

use Galexth\QueryBuilder\Rule;

class Prospect implements Rule
{
    public static function patterns(): array
    {
        return [
            'name' => [
                'query_type' => 'match',
                'fields' => 'name',
                'type' => 'text',
            ],
            'industry' => [
                'query_type' => 'match',
                'fields' => 'industry',
                'type' => 'text',
            ],
            'tags' => [
                'query_type' => 'terms',
                'fields' => 'tags.name',
                'nested' => [
                    'path' => 'tags'
                ],
                'type' => 'text',
            ]
        ];
    }
}
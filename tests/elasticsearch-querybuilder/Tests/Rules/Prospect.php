<?php

namespace Galexth\QueryBuilder\Tests\Rules;

use Elastica\Query\BoolQuery;
use Elastica\Query\Terms;
use Galexth\QueryBuilder\Rule;

class Prospect implements Rule
{
    public function patterns(): array
    {
        return [
            [
                'name' => 'keywords',
                'query_type' => 'multi_match',
                'fields' => ['name', 'first_name', 'last_name'],
                'type' => 'text',
            ],
            [
                'name' => 'name',
                'query_type' => 'match',
                'fields' => ['name'],
                'type' => 'text',
            ],
            [
                'name' => 'industry',
                'query_type' => 'match',
                'fields' => ['industry'],
                'type' => 'text',
            ],
            [
                'name' => 'revenue',
                'query_type' => 'terms',
                'fields' => ['revenue'],
                'type' => 'text',
            ],
            [
                'name' => 'rank\.(?<sub_field>[\w-_]+)$',
                'expression' => true,
                'query_type' => 'terms',
                'fields' => ['ranks.{sub_field}'],
                'type' => 'text',
            ],
            [
                'name' => 'location.country',
                'query_type' => 'terms',
                'fields' => ['locations.country'],
                'nested' => [
                    'path' => 'locations'
                ],
                'type' => 'text',
            ],
            [
                'name' => 'tags',
                'query_type' => 'terms',
                'fields' => ['tags.name'],
                'nested' => [
                    'path' => 'tags'
                ],
                'type' => 'text',
            ],
            [
                'name' => 'persona',
                'query_type' => 'custom',
                'fields' => ['persona_id'],
                'type' => 'text',
                'callback' => function ($values) {
                    $bool = new BoolQuery();
                    return $bool->addFilter(new Terms('asd', (array) $values));
                }
            ],
        ];
    }
}
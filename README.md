# Elasticsearch query builder

Elastica query builder for laravel.

## Rules

Each rules have to implement \Galexth\QueryBuilder\Rule interface with pattern() array:
```php
class MyRule implements Rule
{
    public function patterns(): array
    {
        return [
            [
                'name' => 'location.country', // name of the field in the query (@location.country has ....)
                'query_type' => 'terms', // query type (terms, term, match, multi_match, etc...)
                'fields' => ['locations.country'], // fields to search in
                // nested if necessary
                'nested' => [
                    'path' => 'locations'
                ],
                'type' => 'text', // value type (text, integer, date), text is only supported by now 
            ],
            // custom handler
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
            // using a regular expressions
            [
                'name' => 'rank\.(?<sub_field>[\w-_]+)$',
                'expression' => true,
                'query_type' => 'terms',
                'fields' => ['ranks.{sub_field}'],
                'type' => 'text',
            ],
            ...
        ];
    }
}
```
## Query

Use queries like:
```php
$query = '@name is Prof. Johan Schoen and @industry is Network Security Hardware & Software';
$query = '@name is "John" and @industry is \'Medicine\' or (@industry is Oil and @name is Chris) or @name is Jesus';
$query = '@revenue gte 123 and @revenue lt 523';
...
```
More examples could be found in tests.

```php
    $builder = new Builder(new MyRule);

    $bool = $builder->build($query); // BoolQuery
```
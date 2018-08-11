<?php

namespace Galexth\QueryBuilder;

use Elastica\Query\BoolQuery;

class Builder
{
    /**
     * @var \Galexth\QueryBuilder\Rule
     */
    protected $rule;

    /**
     * Parser constructor.
     *
     * @param \Galexth\QueryBuilder\Rule $rule
     */
    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * @param string $query
     *
     * @return \Elastica\Query\BoolQuery
     * @throws \Exception
     */
    public function run(string $query)
    {
        $parsed = $this->parse($query);

        return $this->buildQuery($parsed);
    }

    /**
     * @param array       $terms
     * @param string|null $lastOperator
     *
     * @return \Elastica\Query\BoolQuery
     * @throws \Exception
     */
    private function buildQuery(array $terms, string $lastOperator = null)
    {
        if (! $lastOperator && count($terms) == 1) {
            $lastOperator = 'and';
        }

        $bool = new BoolQuery();
        $operandPair = [];
        $patterns = $this->rule::patterns();
        foreach ($terms as $key => $item) {
            if ($key % 2 && ! in_array($item, ['and', 'or'])) {
                throw new \Exception('Wrong sequence.');
            }

            if ($item instanceof Expression) {
                if (! isset($patterns[$item->operand])) {
                    throw new \Exception("Unknown operand '{$item->operand}'.");
                }

                $pattern = $patterns[$item->operand];

                $type = $this->getTypeObject($pattern['type']);

                if (! method_exists($type, ($method = camel_case($item->operator)))) {
                    throw new \Exception("Unknown operator '{$item->operator}' in type '{$pattern['type']}'.");
                }

                $operandPair[] = call_user_func_array([$type, $method], [$item, $pattern]);

                if ($lastOperator) {

                    foreach ($operandPair as $operand) {
                        $bool->{$this->getBoolType($lastOperator)}($operand);
                    }

                    $operandPair = [];
                    $lastOperator = null;
                }
            } elseif (is_array($item)) {
                $operandPair[] = $this->buildQuery($item, count($item) == 1 ? $lastOperator : null);

                if ($lastOperator) {

                    foreach ($operandPair as $operand) {
                        $bool->{$this->getBoolType($lastOperator)}($operand);
                    }

                    $operandPair = [];
                    $lastOperator = null;
                }
            } else {
                $lastOperator = $item;
            }
        }

        return $bool;
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    private function getTypeObject(string $type)
    {
        $class = 'Galexth\QueryBuilder\Types\\' . $type;

        return new $class;
    }
    /**
     * @param string $operator
     *
     * @return string
     */
    private function getBoolType(string $operator)
    {
        return $operator == 'and' ? 'addMust' : 'addShould';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    private function parse(string $query)
    {
        $query = strtolower(trim($query));

        if (preg_match('/^\(.+\)$/', $query)) {
            $query = substr($query, 1, -1);
        }

        $query = preg_replace('/^\((?:.+)\)$/', '', $query);

        $pattern = '/\(\s*+@.+?\)+(?=\s+(?:and|or))|\(\s*+@.+?\)+$/';

        $replaced = [];

        $result = preg_replace_callback($pattern, function ($matches) use (&$replaced) {
            $replaced[] = $matches[0];
            return '{$' . count($replaced) . '}';
        }, $query);


        $pattern = '/(and|or)(?=\s+(?:@|{\$\d}))/';

        $parts = preg_split($pattern, $result, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);

        return array_map(function ($item) use ($replaced) {

            if (preg_match('/{\$(\d+)}/', $item, $matches)) {
                return $this->parse($replaced[$matches[1] - 1]);
            }

            if (! in_array($item, ['or', 'and'])) {
                $item = $this->parseExpression(trim($item));
            }

            return $item;
        }, $parts);
    }

    /**
     * @param string $expression
     *
     * @return \Galexth\QueryBuilder\Expression|string
     * @throws \Exception
     */
    private function parseExpression(string $expression)
    {
        preg_match('/@(\w+)\s+(?:(is not empty|is empty)$|((?:has|is)(?:\s+not)?)\s+(.+))/', $expression, $matches);

        //@todo unexpected result
        $matches = array_values(array_filter($matches));

        if (count($matches) < 3) {
            throw new \Exception('Wrong number of segments.');
        }

        $values = isset($matches[3]) ? $this->removeQuotes($matches[3]) : null;

        return new Expression($matches[1], $matches[2], $values);
    }

    /**
     * @param string $values
     *
     * @return string
     */
    private function removeQuotes(string $values)
    {
        preg_match('/(?<=^")(.+)(?="$)/', $values, $matches);

        return $matches[1] ?? $values;
    }
}
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
     * @throws \Galexth\QueryBuilder\BuilderException
     */
    public function build(string $query)
    {
        $terms = $this->parse($query);

        return $this->buildQuery($terms, count($terms) == 1 ? 'and' : null);
    }

    /**
     * @param string $query
     *
     * @return array
     */
    private function parse(string $query)
    {
        $query = trim($query);

        $replaced = [];

        // Replaces expressions into brackets by short codes {$1-9}
        // to parse it recursively
        $result = preg_replace_callback('/\(((?>[^()]+)|(?R))*\)/', function ($matches) use (&$replaced) {

            if (! preg_match($this->getExpressionPattern(), $matches[0])) {
                return $matches[0];
            }

            $replaced[] = substr($matches[0], 1, -1);

            return '{$' . count($replaced) . '}';

        }, $query);

        // Spits query by and|or
        $parts = preg_split(
            '/(and|or)(?=\s+(?:@|{\$\d}))/i', $result, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE
        );

        // Replaces the short codes created previously
        return array_map(function ($item) use ($replaced) {

            // parse expressions under short codes separately
            if (preg_match('/{\$(\d+)}/', $item, $matches)) {
                $subChunk = $this->parse($replaced[$matches[1] - 1]);

                // return only Expression object if there is only one clause inside
                // @todo  make it better
                return count($subChunk) > 1 ? $subChunk : $subChunk[0];
            }

            if (! $this->isBoolOperator($item)) {
                $item = $this->parseExpression(trim($item));
            }

            return $item;

        }, $parts);
    }

    /**
     * Turns 'name has John' into an Expression object
     * @param string $expression
     *
     * @return \Galexth\QueryBuilder\Expression
     * @throws \Galexth\QueryBuilder\BuilderException
     */
    private function parseExpression(string $expression)
    {
        preg_match($this->getExpressionPattern(), $expression, $matches);

        //@todo unexpected result
        $matches = array_values(array_filter($matches));

        if (count($matches) < 3) {
            throw new BuilderException('Wrong number of segments.');
        }

        $values = [];

        if (isset($matches[3])) {
            $values = $this->parseValues($matches[3]);
        }

        return new Expression($matches[1], $matches[2], $values);
    }

    /**
     * @param array       $terms
     * @param string|null $lastOperator
     *
     * @return \Elastica\Query\BoolQuery
     * @throws \Galexth\QueryBuilder\BuilderException
     */
    private function buildQuery(array $terms, string $lastOperator = null)
    {
        $bool = new BoolQuery();
        $operandPair = [];

        foreach ($terms as $key => $item) {
            if ($key % 2 && ! $this->isBoolOperator($item)) {
                throw new BuilderException('Wrong sequence.');
            }

            if ($item instanceof Expression) {
                $pattern = $this->getPattern($item->operand);

                $type = $this->getTypeObject(ucfirst($pattern['type']));

                if (! method_exists($type, ($method = camel_case($item->operator)))) {
                    throw new BuilderException("Unknown operator '{$item->operator}' in type '{$pattern['type']}'.");
                }

                $query = call_user_func_array([$type, $method], [$item, $pattern]);

                if (isset($pattern['nested'])) {
                    $query = $type->nest($pattern['nested']['path'], $query);
                }

                $operandPair[] = $query;

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
     * @param string $operand
     *
     * @return mixed
     * @throws \Galexth\QueryBuilder\BuilderException
     */
    private function getPattern(string $operand)
    {
        foreach ($this->rule->patterns() as $pattern) {
            if (isset($pattern['expression']) && preg_match("/{$pattern['name']}/", $operand, $matches)) {
                // Check for sub fields inside of an expression
                // and resolve actual fields if 'expression' is set
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        foreach ($pattern['fields'] as &$field) {
                            $field = str_replace("{{$key}}", $match, $field);
                        }
                    }
                }
                return $pattern;
            }

            if ($operand == $pattern['name']) {
                return $pattern;
            }
        }

        throw new BuilderException("Unknown operand '{$operand}'.");
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
        return strtolower($operator) == 'and' ? 'addMust' : 'addShould';
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isBoolOperator(string $value)
    {
        return in_array(strtolower($value), ['and', 'or']);
    }

    /**
     * @param string $values
     *
     * @return array
     */
    private function parseValues(string $values)
    {
        if (count($result = $this->splitValues($values)) < 2 && ! $this->removeQuotes($values, true)) {
            $result = $this->splitValues($values, '/\s*+,\s*+/');
        }

        return array_map(function ($item) {
            return $this->removeQuotes($item);
        }, $result);

    }

    /**
     * Removed the quotes ",' from start and end of a value
     *
     * @param string $values
     * @param bool   $match
     *
     * @return string|bool
     */
    private function removeQuotes(string $values, bool $match = false)
    {
        preg_match('/(?<=["\'])(.+)(?=["\']$)/', $values, $matches);

        if ($match) {
            return isset($matches[1]);
        }

        return $matches[1] ?? $values;
    }

    /**
     * @param string $values
     * @param string $pattern
     *
     * @return array
     */
    private function splitValues(string $values, string $pattern = '/(?<=["\'])\s*+,\s*+(?=["\'])/')
    {
        return preg_split($pattern, $values, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @return string
     */
    private function getExpressionPattern()
    {
        return '/@([\w.]+)\s+(?:(is not empty|is empty)$|((?:has|in||is|lt|lte|gt|gte|between|less|more|before|after|not have)(?:\s+not)?)\s+(.+))/i';
    }
}
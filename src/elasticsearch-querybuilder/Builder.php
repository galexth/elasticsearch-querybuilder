<?php

namespace Galexth\QueryBuilder;

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
     * @return array
     */
    public function run(string $query)
    {
        return $this->parse($query);

        //@todo
    }

    /**
     * @param string $query
     *
     * @return array
     */
    private function parse(string $query)
    {
        $query = preg_replace('/^\(|\)$/', '', strtolower(trim($query)));

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
        preg_match('/@(\w+)\s+(has|is)\s+(.+)/', $expression, $matches);

        if (count($matches) < 4) {
            throw new \Exception('Wrong number of segments.');
        }

        return new Expression($matches[1], $matches[2], $this->removeQuotes($matches[3]));
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
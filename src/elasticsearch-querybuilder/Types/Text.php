<?php

namespace Galexth\QueryBuilder\Types;

use Galexth\QueryBuilder\AbstractType;
use Galexth\QueryBuilder\BuilderException;
use Galexth\QueryBuilder\Expression;
use Illuminate\Support\Arr;

class Text extends AbstractType
{
    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function is(Expression $expression, array $pattern)
    {
        return $this->callMethod($pattern['query_type'], $pattern, $expression->values);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return \Elastica\Query\BoolQuery
     */
    public function isNot(Expression $expression, array $pattern)
    {
        return $this->not($expression, $pattern, 'is');
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function isEmpty(Expression $expression, array $pattern)
    {
        return $this->not($expression, $pattern, 'isNotEmpty');
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function isBlank(Expression $expression, array $pattern)
    {
        return $this->isEmpty($expression, $pattern);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function isNotEmpty(Expression $expression, array $pattern)
    {
        return $this->callMethod('exists', $pattern, $expression->values);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function has(Expression $expression, array $pattern)
    {
        return $this->callMethod($pattern['query_type'], $pattern, $expression->values);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return \Elastica\Query\BoolQuery
     */
    public function hasNot(Expression $expression, array $pattern)
    {
        return $this->not($expression, $pattern, 'has');
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return \Elastica\Query\BoolQuery
     */
    public function notHave(Expression $expression, array $pattern)
    {
        return $this->hasNot($expression, $pattern);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function in(Expression $expression, array $pattern)
    {
        return $this->has($expression, $pattern);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     * @param string                           $method
     *
     * @return mixed
     */
    public function not(Expression $expression, array $pattern, string $method)
    {
        return $this->{$method}($expression, $pattern);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function lt(Expression $expression, array $pattern)
    {
        return $this->callMethod('range', $pattern, [__FUNCTION__ => Arr::first($expression->values)]);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function lte(Expression $expression, array $pattern)
    {
        return $this->callMethod('range', $pattern, [__FUNCTION__ => Arr::first($expression->values)]);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function gt(Expression $expression, array $pattern)
    {
        return $this->callMethod('range', $pattern, [__FUNCTION__ => Arr::first($expression->values)]);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function gte(Expression $expression, array $pattern)
    {
        return $this->callMethod('range', $pattern, [__FUNCTION__ => Arr::first($expression->values)]);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function more(Expression $expression, array $pattern)
    {
        return $this->gte($expression, $pattern);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function after(Expression $expression, array $pattern)
    {
        return $this->gte($expression, $pattern);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function less(Expression $expression, array $pattern)
    {
        return $this->lte($expression, $pattern);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function before(Expression $expression, array $pattern)
    {
        return $this->lte($expression, $pattern);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     * @throws \Galexth\QueryBuilder\BuilderException
     */
    public function between(Expression $expression, array $pattern)
    {
        $values = $expression->values;
        // legacy support for old format 'value and value'
        // @todo remove after updated
        if (count($values) < 2 && preg_match('/and/i', $values[0])) {
            $values = preg_split('/\s*+and\s*+/i', $values[0], -1, PREG_SPLIT_NO_EMPTY);
        }

        if (count($values) < 2) {
            throw new BuilderException('Wrong number of parameters.');
        }

        return $this->callMethod('range', $pattern, [
            'gte' => $values[0], 'lte' => $values[1],
        ]);
    }
}
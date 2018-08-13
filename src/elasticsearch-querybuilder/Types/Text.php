<?php

namespace Galexth\QueryBuilder\Types;

use Elastica\Query\BoolQuery;
use Galexth\QueryBuilder\AbstractType;
use Galexth\QueryBuilder\Expression;

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
        return $this->not($expression, $pattern, 'is not empty');
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
     * @return \Elastica\Query\BoolQuery
     */
    public function not(Expression $expression, array $pattern, string $method)
    {
        $bool = new BoolQuery();
        return $bool->addMustNot($this->{$method}($expression, $pattern));
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function lt(Expression $expression, array $pattern)
    {
        return $this->callMethod('range', $pattern, [__FUNCTION__ => $expression->values]);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function lte(Expression $expression, array $pattern)
    {
        return $this->callMethod('range', $pattern, [__FUNCTION__ => $expression->values]);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function gt(Expression $expression, array $pattern)
    {
        return $this->callMethod('range', $pattern, [__FUNCTION__ => $expression->values]);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function gte(Expression $expression, array $pattern)
    {
        return $this->callMethod('range', $pattern, [__FUNCTION__ => $expression->values]);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function between(Expression $expression, array $pattern)
    {
        return $this->callMethod('range', $pattern, [
            'gte' => $expression->values[0], 'lte' => $expression->values[1],
        ]);
    }
}
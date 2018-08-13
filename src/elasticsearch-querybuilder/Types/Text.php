<?php

namespace Galexth\QueryBuilder\Types;

use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Match;
use Elastica\Query\MatchPhrase;
use Elastica\Query\MultiMatch;
use Elastica\Query\Range;
use Elastica\Query\Term;
use Elastica\Query\Terms;
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
    public function has(Expression $expression, array $pattern)
    {
        return $this->callMethod($pattern['query_type'], $pattern, $expression->values);
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
     * @return \Elastica\Query\BoolQuery
     */
    public function hasNot(Expression $expression, array $pattern)
    {
        return $this->not($expression, $pattern, 'has');
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

    /**
     * @param array $pattern
     * @param        $values
     *
     * @return \Elastica\Query\Match
     */
    protected function getMatchQuery(array $pattern, $values)
    {
        return new Match($pattern['fields'][0], $values);
    }

    /**
     * @param array $pattern
     * @param        $values
     *
     * @return \Elastica\Query\MatchPhrase
     */
    protected function getMatchPhraseQuery(array $pattern, $values)
    {
        return new MatchPhrase($pattern['fields'][0], $values);
    }

    /**
     * @param array $pattern
     * @param       $values
     *
     * @return \Elastica\Query\MultiMatch
     */
    protected function getMultiMatchQuery(array $pattern, $values)
    {
        $query = new MultiMatch();
        $query->setFields($pattern['fields']);
        return $query->setQuery($values);
    }

    /**
     * @param array $pattern
     * @param        $values
     *
     * @return \Elastica\Query\Term|\Elastica\Query\Terms
     */
    protected function getTermsQuery(array $pattern, $values)
    {
        $values = array_map(function ($value) {
            return trim($value);
        }, preg_split('/\s*+,\s*+/', $values));

        if (count($values) > 1) {
            return new Terms($pattern['fields'][0], $values);
        }

        return new Term([$pattern['fields'][0] => $values[0]]);
    }

    /**
     * @param array $pattern
     * @param        $values
     *
     * @return \Elastica\Query\Term
     */
    protected function getTermQuery(array $pattern, $values)
    {
        return new Term([$pattern['fields'][0] => $values]);
    }

    /**
     * @param array $pattern
     *
     * @return \Elastica\Query\Exists
     */
    protected function getExistsQuery(array $pattern)
    {
        return new Exists($pattern['fields'][0]);
    }

    /**
     * @param array $pattern
     * @param       $values
     *
     * @return \Elastica\Query\Range
     */
    protected function getRangeQuery(array $pattern, $values)
    {
        return new Range($pattern['fields'][0], $values);
    }
}
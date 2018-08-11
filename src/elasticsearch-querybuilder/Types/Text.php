<?php

namespace Galexth\QueryBuilder\Types;

use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Match;
use Elastica\Query\MatchPhrase;
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
        return $this->callMethod($pattern['query_type'], $expression->operand, $expression->values);
    }

    /**
     * @param \Galexth\QueryBuilder\Expression $expression
     * @param array                            $pattern
     *
     * @return mixed
     */
    public function isNotEmpty(Expression $expression, array $pattern)
    {
        return $this->callMethod('exists', $expression->operand, $expression->values);
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
        $values = array_map(function ($value) {
            return trim($value);
        }, preg_split('/\s+,\s+/', $expression->values));

        return $this->callMethod($pattern['query_type'], $expression->operand, $values);
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
     * @param string $name
     * @param        $values
     *
     * @return \Elastica\Query\Match
     */
    protected function getMatchQuery(string $name, $values)
    {
        return new Match($name, $values);
    }

    /**
     * @param string $name
     * @param        $values
     *
     * @return \Elastica\Query\MatchPhrase
     */
    protected function getMatchPhraseQuery(string $name, $values)
    {
        return new MatchPhrase($name, $values);
    }

    /**
     * @param string $name
     * @param        $values
     *
     * @return \Elastica\Query\Term|\Elastica\Query\Terms
     */
    protected function getTermsQuery(string $name, $values)
    {
        $values = (array) $values;

        if (count($values) > 1) {
            return new Terms($name, $values);
        }

        return new Term([$name => $values[0]]);
    }

    /**
     * @param string $name
     * @param        $values
     *
     * @return \Elastica\Query\Term
     */
    protected function getTermQuery(string $name, $values)
    {
        return new Term([$name => $values]);
    }

    /**
     * @param string $name
     *
     * @return \Elastica\Query\Exists
     */
    protected function getExistsQuery(string $name)
    {
        return new Exists($name);
    }
}
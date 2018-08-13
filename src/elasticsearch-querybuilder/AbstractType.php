<?php

namespace Galexth\QueryBuilder;


use Carbon\Carbon;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Exists;
use Elastica\Query\Match;
use Elastica\Query\MatchPhrase;
use Elastica\Query\MultiMatch;
use Elastica\Query\Nested;
use Elastica\Query\Range;
use Elastica\Query\Term;
use Elastica\Query\Terms;

abstract class AbstractType
{
    /**
     * @param string $type
     *
     * @return string
     */
    protected function getMethod(string $type)
    {
        return 'get'.camel_case($type).'Query';
    }

    /**
     * @param string $queryType
     * @param array  $pattern
     * @param        $values
     *
     * @return mixed
     */
    protected function callMethod(string $queryType, array $pattern, $values)
    {
        return call_user_func_array([$this, $this->getMethod($queryType)], [$pattern, $values]);
    }

    /**
     * @param string                        $path
     * @param \Elastica\Query\AbstractQuery $query
     *
     * @return \Elastica\Query\Nested
     */
    public function nest(string $path, AbstractQuery $query)
    {
        $nested = new Nested();
        $nested->setPath($path);
        return $nested->setQuery($query);
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
     * @throws \Exception
     */
    protected function getRangeQuery(array $pattern, $values)
    {
        foreach ($values as $key => $value) {
            if (is_numeric($value)) {
                $values[$key] = (int) $value;
            } else {
                try {
                    Carbon::parse($value);
                } catch (\Exception $e) {
                    throw new \Exception('Wrong date format in between clause.');
                }
            }
        }

        return new Range($pattern['fields'][0], $values);
    }

    /**
     * @param array $pattern
     * @param       $values
     *
     * @return mixed
     */
    protected function getCustomQuery(array $pattern, $values)
    {
        return call_user_func($pattern['callback'], $values);
    }
}
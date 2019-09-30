<?php

namespace Galexth\QueryBuilder;


use Carbon\Carbon;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Match;
use Elastica\Query\MatchPhrase;
use Elastica\Query\MultiMatch;
use Elastica\Query\Nested;
use Elastica\Query\Range;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Illuminate\Support\Str;

abstract class AbstractType
{
    /**
     * @param array $pattern
     * @param array $values
     *
     * @return \Elastica\Query\Match
     */
    protected function getMatchQuery(array $pattern, array $values)
    {
        return array_map(function ($value) use ($pattern) {
            return new Match($pattern['fields'][0], $value);
        }, $values);
    }

    /**
     * @param array $pattern
     * @param array $values
     *
     * @return \Elastica\Query\MatchPhrase
     */
    protected function getMatchPhraseQuery(array $pattern, array $values)
    {
        return array_map(function ($value) use ($pattern) {
            return new MatchPhrase($pattern['fields'][0], $value);
        }, $values);
    }

    /**
     * @param array $pattern
     * @param array $values
     *
     * @return \Elastica\Query\MultiMatch
     */
    protected function getMultiMatchQuery(array $pattern, array $values)
    {
        $query = new MultiMatch();
        $query->setFields($pattern['fields']);

        return $query->setQuery(implode(' ', $values));
    }

    /**
     * @param array $pattern
     * @param array $values
     *
     * @return \Elastica\Query\Term|\Elastica\Query\Terms
     */
    protected function getTermsQuery(array $pattern, array $values)
    {
        if (count($values) > 1) {
            return new Terms($pattern['fields'][0], $values);
        }

        return new Term([$pattern['fields'][0] => $values[0]]);
    }

    /**
     * @param array $pattern
     * @param array $values
     *
     * @return \Elastica\Query\Term
     */
    protected function getTermQuery(array $pattern, array $values)
    {
        return new Term([$pattern['fields'][0] => $values[0]]);
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
     * @param array $values
     *
     * @return \Elastica\Query\Range
     * @throws \Galexth\QueryBuilder\BuilderException
     */
    protected function getRangeQuery(array $pattern, array $values)
    {
        foreach ($values as $key => $value) {
            if (is_numeric($value)) {
                $values[$key] = (int) $value;
            } else {
                try {
                    $values[$key] = Carbon::parse($value)->toDateTimeString();
                } catch (\Exception $e) {
                    throw new BuilderException('Wrong date format in between clause.');
                }
            }
        }

        return new Range($pattern['fields'][0], $values);
    }

    /**
     * @param array $pattern
     * @param array $values
     *
     * @return mixed
     */
    protected function getCustomQuery(array $pattern, array $values)
    {
        return call_user_func($pattern['callback'], $values);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getMethod(string $type)
    {
        return 'get'.Str::camel($type).'Query';
    }

    /**
     * @param string $queryType
     * @param array  $pattern
     * @param        $values
     *
     * @return mixed
     */
    protected function callMethod(string $queryType, array $pattern, array $values)
    {
        return call_user_func_array([$this, $this->getMethod($queryType)], [$pattern, $values]);
    }

    /**
     * @param string                              $path
     * @param \Elastica\Query\AbstractQuery|array $query
     *
     * @return \Elastica\Query\Nested
     */
    public function nest(string $path, $query)
    {
        if (is_array($query)) {
            $bool = new BoolQuery();
            $query = $bool->addMust($query);
        }

        $nested = new Nested();
        $nested->setPath($path);
        $nested->setQuery($query);

        return $nested;
    }

}
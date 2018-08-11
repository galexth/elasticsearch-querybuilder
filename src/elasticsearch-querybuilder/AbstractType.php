<?php

namespace Galexth\QueryBuilder;


use Elastica\Query\AbstractQuery;
use Elastica\Query\Nested;

abstract class AbstractType
{
    /**
     * @param string $type
     *
     * @return string
     */
    protected function getMethod(string $type)
    {
        return 'get'.ucfirst($type).'Query';
    }

    /**
     * @param string $queryType
     * @param string $operand
     * @param        $values
     *
     * @return mixed
     */
    protected function callMethod(string $queryType, string $operand, $values)
    {
        return call_user_func_array([$this, $this->getMethod($queryType)], [$operand, $values]);
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
}
<?php

namespace Galexth\QueryBuilder;


class Expression
{
    /**
     * @var string
     */
    public $operand;
    /**
     * @var string
     */
    public $operator;
    /**
     * @var array
     */
    public $values;

    /**
     * Expression constructor.
     *
     * @param string $operand
     * @param string $operator
     * @param array  $values
     */
    public function __construct(string $operand, string $operator, string $values)
    {
        $this->operand = $operand;
        $this->operator = $operator;
        $this->values = $values;
    }
}
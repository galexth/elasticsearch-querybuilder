<?php

namespace Galexth\QueryBuilder;

interface Rule
{
    /**
     * @return array
     */
    public function patterns(): array;
}
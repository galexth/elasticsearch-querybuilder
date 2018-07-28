<?php

namespace Galexth\QueryBuilder;

interface Rule
{
    /**
     * @return array
     */
    public static function patterns(): array;
}
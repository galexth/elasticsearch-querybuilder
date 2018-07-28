<?php

namespace Galexth\QueryBuilder\Tests;


abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass()
    {
        \Kint::$max_depth = 10;
    }

    /**
     * @param array  $keys
     * @param array  $array
     * @param string $message
     */
    public function assertArrayHasKeys(array $keys, array $array, string $message = '')
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message);
        }
    }

}

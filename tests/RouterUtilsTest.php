<?php

namespace RouterUtils;

class RouterUtilsTest extends \PHPUnit_Framework_TestCase
{
    public static $src = ['a', 'b', 'c', 'd'];

    public function testFilterOnlyString()
    {
        $result = \Sidvind\PHPRoutes\Utils::filterOnly(static::$src, 'a');
        $this->assertEquals(['a'], array_values($result));
    }

    public function testFilterOnlyArray()
    {
        $result = \Sidvind\PHPRoutes\Utils::filterOnly(static::$src, ['a', 'c']);
        $this->assertEquals(['a', 'c'], array_values($result));
    }

    public function testFilterExceptString()
    {
        $result = \Sidvind\PHPRoutes\Utils::filterExcept(static::$src, 'b');
        $this->assertEquals(['a', 'c', 'd'], array_values($result));
    }

    public function testFilterExceptArray()
    {
        $result = \Sidvind\PHPRoutes\Utils::filterExcept(static::$src, ['a', 'c']);
        $this->assertEquals(['b', 'd'], array_values($result));
    }
}

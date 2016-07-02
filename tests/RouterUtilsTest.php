<?php

namespace RouterUtils;

class RouterUtilsTest extends \PHPUnit_Framework_TestCase {
	public static $src = ['a', 'b', 'c', 'd'];

	public function test_filter_only_string(){
		$result = \Sidvind\PHPRoutes\Utils::filter_only(static::$src, 'a');
		$this->assertEquals(['a'], array_values($result));
	}

	public function test_filter_only_array(){
		$result = \Sidvind\PHPRoutes\Utils::filter_only(static::$src, ['a', 'c']);
		$this->assertEquals(['a', 'c'], array_values($result));
	}

	public function test_filter_except_string(){
		$result = \Sidvind\PHPRoutes\Utils::filter_except(static::$src, 'b');
		$this->assertEquals(['a', 'c', 'd'], array_values($result));
	}

	public function test_filter_except_array(){
		$result = \Sidvind\PHPRoutes\Utils::filter_except(static::$src, ['a', 'c']);
		$this->assertEquals(['b', 'd'], array_values($result));
	}
}

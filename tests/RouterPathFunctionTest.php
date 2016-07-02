<?php

namespace RouterPath;

class RouterPathFunctionTest extends \PHPUnit_Framework_TestCase {
	public $router;

	public function setUp(){
		$this->router = new \Sidvind\PHPRoutes\Router();
	}

	protected function path_function($pattern){
		return $this->router->method($pattern, 'GET', ['as' => 'test_pattern']);
	}

	public function test_no_args(){
		$func = $this->path_function('foo');
		$this->assertEquals('/foo', $func());
	}

	public function test_positional_arg(){
		$func = $this->path_function('foo/:id');
		$this->assertEquals('/foo/7', $func(7));
	}

	public function test_named_arg(){
		$func = $this->path_function('foo/:id');
		$this->assertEquals('/foo/8', $func(['id' => 8]));
	}

	public function test_object_arg(){
		$func = $this->path_function('foo/:id');
		$this->assertEquals('/foo/9', $func((object)['id' => 9]));
	}

	public function test_missing_args(){
		$func = $this->path_function('foo/:id');
		$this->expectException(\BadFunctionCallException::class);
		$this->assertEquals('/foo/:id', $func());
	}

	public function test_multiple_args(){
		$func = $this->path_function('foo/:id/baz/:spam');
		$this->assertEquals('/foo/3/baz/4', $func(3, 4));
		$this->assertEquals('/foo/3/baz/4', $func(['id' => 3, 'spam' => 4]));
		$this->assertEquals('/foo/3/baz/4', $func((object)['id' => 3, 'spam' => 4]));
	}
}

<?php

namespace RouterName;

class Router extends \Sidvind\PHPRoutes\Router {
	public function test_name($pattern){
		$this->method($pattern, 'GET', []);
		return $this->patterns[0]->action;
	}
}

class RouterNameTest extends \PHPUnit_Framework_TestCase {
	public $router;

	public function setUp(){
		$this->router = new Router();
	}

	public function test_blank(){
		$this->assertEquals('index', $this->router->test_name(''));
	}

	public function test_name(){
		$this->assertEquals('foo', $this->router->test_name('foo'));
	}

	public function test_leading_slash(){
		$this->assertEquals('foo', $this->router->test_name('/foo'));
	}

	public function test_separator(){
		$this->assertEquals('foo_bar', $this->router->test_name('foo/bar'));
	}

	public function test_variable(){
		$this->assertEquals('foo', $this->router->test_name('foo/:id'));
	}

	public function test_variable_separator(){
		$this->assertEquals('foo_baz', $this->router->test_name('foo/:id/baz'));
	}
}

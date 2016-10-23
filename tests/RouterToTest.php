<?php

class Router extends Sidvind\PHPRoutes\Router {
	/* expose */
	public function parseTo($str){
		return parent::parseTo($str);
	}
}

class RouterToTest extends PHPUnit_Framework_TestCase {
	public $router;

	public function setUp(){
		$this->router = new Router();
	}

	public function test_blank(){
		$this->assertEquals(['Index', 'index'], $this->router->parseTo(''));
	}

	public function test_only_hash(){
		$this->assertEquals(['Index', 'index'], $this->router->parseTo('#'));
	}

	public function test_only_controller(){
		$this->assertEquals(['Foo', 'index'], $this->router->parseTo('Foo'));
	}

	public function test_only_action(){
		$this->assertEquals(['Index', 'foo'], $this->router->parseTo('#foo'));
	}

	public function test_controller_action(){
		$this->assertEquals(['Foo', 'bar'], $this->router->parseTo('Foo#bar'));
	}

	public function test_malformed2(){
		$this->expectException(\BadFunctionCallException::class);
		$this->assertEquals(['Index', 'index'], $this->router->parseTo('foo##bar'));
	}
}

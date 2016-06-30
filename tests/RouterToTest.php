<?php

class Router extends Sidvind\PHPRoutes\Router {
	/* expose */
	public function parse_to($str){
		return parent::parse_to($str);
	}
}

class RouterToTest extends PHPUnit_Framework_TestCase {
	public $router;

	public function setUp(){
		$this->router = new Router();
	}

	public function test_blank(){
		$this->assertEquals(['Index', 'index'], $this->router->parse_to(''));
	}

	public function test_only_hash(){
		$this->assertEquals(['Index', 'index'], $this->router->parse_to('#'));
	}

	public function test_only_controller(){
		$this->assertEquals(['Foo', 'index'], $this->router->parse_to('Foo'));
	}

	public function test_only_action(){
		$this->assertEquals(['Index', 'foo'], $this->router->parse_to('#foo'));
	}

	public function test_controller_action(){
		$this->assertEquals(['Foo', 'bar'], $this->router->parse_to('Foo#bar'));
	}
}

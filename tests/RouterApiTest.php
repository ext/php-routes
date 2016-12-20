<?php

namespace RouterApi;

class Router extends \Sidvind\PHPRoutes\Router {
	public function num_routes(){
		return count($this->patterns);
	}
}

class RouterApiFunctionTest extends \PHPUnit_Framework_TestCase {
	public $router;

	public function setUp(){
		$this->router = new Router();
	}

	public function test_clear(){
		$this->assertEquals(0, $this->router->num_routes());
		$this->router->method('foo', 'GET', []);
		$this->assertEquals(1, $this->router->num_routes());
		$this->router->clear();
		$this->assertEquals(0, $this->router->num_routes());
	}

	public function test_formatRoutes(){
		$this->router->method('foo', 'GET', []);
		$this->assertStringEqualsFile(__DIR__ . "/formatted.txt", $this->router->formatRoutes());
	}

	public function test_formatRoutesVerbose(){
		$this->router->method('foo', 'GET', []);
		$this->assertStringEqualsFile(__DIR__ . "/formatted-verbose.txt", $this->router->formatRoutes(true));
	}

	public function test_formatRoutes_empty(){
		$this->assertEquals('', $this->router->formatRoutes());
	}

	public function test_printRoutes_empty(){
		$this->router->method('foo', 'GET', []);
		ob_start();
		$this->router->printRoutes();
		$string = ob_get_contents();
		ob_end_clean();
		$this->assertStringEqualsFile(__DIR__ . "/formatted.txt", $this->router->formatRoutes());
	}
}

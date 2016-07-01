<?php

namespace RouterDispatch;

class RouterDispatchFunctionTest extends \PHPUnit_Framework_TestCase {
	public $router;

	public function setUp(){
		$this->router = new \Sidvind\PHPRoutes\Router();
	}

	protected function path_function($pattern){
		return $this->router->method($pattern, 'GET', ['as' => 'test_pattern']);
	}

	public function test_simple(){
		$this->router->method('foo', 'GET', ['to' => 'Test#action']);
		$match = $this->router->match('/foo', 'GET');
		$this->assertTrue((boolean)$match);
		$this->assertEquals('Test', $match->controller);
		$this->assertEquals('action', $match->action);
		$this->assertEquals([], $match->args);
		$this->assertEquals(false, $match->format);
	}

	public function test_method(){
		$this->router->method('foo', 'PATCH', ['to' => 'Test#action']);
		$match = $this->router->match('/foo', 'PATCH');
		$this->assertTrue((boolean)$match);
		$this->assertEquals('Test', $match->controller);
		$this->assertEquals('action', $match->action);
		$this->assertEquals([], $match->args);
		$this->assertEquals(false, $match->format);
	}

	public function test_wrong_method(){
		$this->router->method('foo', 'PATCH', ['to' => 'Test#action']);
		$match = $this->router->match('/foo', 'GET');
		$this->assertFalse((boolean)$match);
	}

	public function test_args(){
		$this->router->method('foo/:id', 'GET', ['to' => 'Test#action']);
		$match = $this->router->match('/foo/7', 'GET');
		$this->assertTrue((boolean)$match);
		$this->assertEquals('Test', $match->controller);
		$this->assertEquals('action', $match->action);
		$this->assertEquals(['id' => 7], $match->args);
		$this->assertEquals(false, $match->format);
	}

	public function test_format(){
		$this->router->method('foo', 'GET', ['to' => 'Test#action']);
		$match = $this->router->match('/foo.json', 'GET');
		$this->assertTrue((boolean)$match);
		$this->assertEquals('Test', $match->controller);
		$this->assertEquals('action', $match->action);
		$this->assertEquals([], $match->args);
		$this->assertEquals('application/json', $match->format);
	}

	public function test_scope(){
		$this->router->scope('foo', [], function($r){
			$r->method('bar', 'GET', ['to' => 'Test#action']);
		});
		$this->assertMatch('/foo/bar', 'GET', 'Test', 'action');
	}

	public function test_scope_variable(){
		$this->router->scope(':lang', [], function($r){
			$r->method('bar', 'GET', ['to' => 'Test#action']);
		});
		$this->assertMatch('/en/bar', 'GET', 'Test', 'action', ['lang' => 'en']);
	}

	protected function assertMatch($url, $method, $controller, $action, array $args=[]){
		$match = $this->router->match($url, $method);
		$this->assertTrue((boolean)$match, "URL {$url} should match a route");
		$this->assertEquals($controller, $match->controller);
		$this->assertEquals($action, $match->action);
		$this->assertEquals($args, $match->args);
	}
}

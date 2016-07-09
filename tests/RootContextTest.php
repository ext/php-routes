<?php

use \Sidvind\PHPRoutes\Router;
use \Sidvind\PHPRoutes\RootContext;

class RootContextTest extends PHPUnit_Framework_TestCase {
	public $router;
	public $context;

	public function setUp(){
		$this->router = $this->createMock('Router');
		$this->context = new RootContext($this->router);
	}

	public function testGet(){
		$this->router->expects($this->once())->method('method')->with(
			$this->equalTo('pattern'),
			$this->equalTo('GET'),
			$this->equalTo([1])
		);
		$this->context->get('pattern', [1]);
	}

	public function testPost(){
		$this->router->expects($this->once())->method('method')->with(
			$this->equalTo('pattern'),
			$this->equalTo('POST'),
			$this->equalTo([2])
		);
		$this->context->post('pattern', [2]);
	}

	public function testPatch(){
		$this->router->expects($this->once())->method('method')->with(
			$this->equalTo('pattern'),
			$this->equalTo('PATCH'),
			$this->equalTo([3])
		);
		$this->context->patch('pattern', [3]);
	}

	public function testPut(){
		$this->router->expects($this->once())->method('method')->with(
			$this->equalTo('pattern'),
			$this->equalTo('PUT'),
			$this->equalTo([4])
		);
		$this->context->put('pattern', [4]);
	}

	public function testDelete(){
		$this->router->expects($this->once())->method('method')->with(
			$this->equalTo('pattern'),
			$this->equalTo('DELETE'),
			$this->equalTo([5])
		);
		$this->context->delete('pattern', [5]);
	}

	public function testResource(){
		$this->router->expects($this->once())->method('resource')->with(
			$this->equalTo('pattern'),
			$this->equalTo([6]),
			$this->anything()
		);
		$this->context->resource('pattern', [6], function(){});
	}

	public function testScope(){
		$this->router->expects($this->once())->method('scope')->with(
			$this->equalTo('pattern'),
			$this->equalTo([7]),
			$this->anything()
		);
		$this->context->scope('pattern', [7], function(){});
	}
}

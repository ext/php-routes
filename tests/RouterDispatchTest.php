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
		$this->router->method('bar', 'GET', []);
		$this->assertMatch('/foo', 'GET', 'Test', 'action');
		$this->assertMatch('/bar', 'GET', 'Index', 'bar');
	}

	public function test_method(){
		$this->router->method('foo', 'PATCH', ['to' => 'Test#action']);
		$this->assertMatch('/foo', 'PATCH', 'Test', 'action');
	}

	public function test_head(){
		$this->router->method('foo', 'GET', ['to' => 'Test#action']);
		$this->assertMatch('/foo', 'GET', 'Test', 'action');
		$this->assertMatch('/foo', 'HEAD', 'Test', 'action');
	}

	public function test_wrong_method(){
		$this->router->method('foo', 'PATCH', ['to' => 'Test#action']);
		$match = $this->router->match('/foo', 'GET');
		$this->assertFalse((boolean)$match);
	}

	public function test_args(){
		$this->router->method('foo/:id', 'GET', ['to' => 'Test#action']);
		$this->assertMatch('/foo/7', 'GET', 'Test', 'action', ['id' => 7]);
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

	public function test_format_mimetype(){
		$this->router->method('foo', 'GET', ['to' => 'Test#action']);
		$this->assertEquals('text/html', $this->router->match('/foo.html', 'GET')->format);
		$this->assertEquals('application/json', $this->router->match('/foo.json', 'GET')->format);
		$this->assertEquals('text/markdown', $this->router->match('/foo.md', 'GET')->format);
		$this->assertEquals('text/plain', $this->router->match('/foo.txt', 'GET')->format);
		$this->assertEquals('application/xml', $this->router->match('/foo.xml', 'GET')->format);
		$this->assertEquals('image/svg+xml', $this->router->match('/foo.svg', 'GET')->format);
	}

	public function test_scope(){
		$this->router->scope('foo', [], function($r){
			$r->method('bar', 'GET', ['to' => 'Test#action']);
			$r->method('baz', 'GET', []);
		});
		$this->assertMatch('/foo/bar', 'GET', 'Test', 'action');
		$this->assertMatch('/foo/baz', 'GET', 'Index', 'baz');
	}

	public function test_scope_variable(){
		$this->router->scope(':lang', [], function($r){
			$r->method('bar', 'GET', ['to' => 'Test#action']);
			$r->method('baz', 'GET', []);
		});
		$this->assertMatch('/en/bar', 'GET', 'Test', 'action', ['lang' => 'en']);
		$this->assertMatch('/en/baz', 'GET', 'Index', 'baz', ['lang' => 'en']);
	}

	public function test_scope_nested(){
		$this->router->scope('foo', [], function($r){
			$r->scope('bar', [], function($r){
				$r->method('spam', 'GET', ['to' => 'Test#action']);
				$r->method('ham', 'GET', []);
			});
		});
		$this->assertMatch('/foo/bar/spam', 'GET', 'Test', 'action');
		$this->assertMatch('/foo/bar/ham', 'GET', 'Index', 'ham');
	}

	public function test_scope_nested_to(){
		$this->router->scope('foo', [], function($r){
			$r->scope('bar', ['to' => 'Bar'], function($r){
				$r->method('baz', 'GET', []);
			});
		});
		$this->assertMatch('/foo/bar/baz', 'GET', 'Bar', 'baz');
	}

	public function test_scope_resource_to(){
		$this->router->scope('admin', ['to' => 'Admin'], function($r){
			$r->resource('post');
		});
		$this->assertMatch('/admin/post', 'GET', 'AdminPost', 'index');
	}

	public function test_scope_resource(){
		$this->router->scope('foo', [], function($r){
			$r->resource('article');
		});
		$this->assertMatch('/foo/article', 'GET', 'Article', 'index');
		$this->assertMatch('/foo/article', 'POST', 'Article', 'create');
		$this->assertMatch('/foo/article/7', 'GET', 'Article', 'show', ['id' => 7]);
		$this->assertMatch('/foo/article/7', 'PUT', 'Article', 'update', ['id' => 7]);
		$this->assertMatch('/foo/article/7', 'PATCH', 'Article', 'update', ['id' => 7]);
		$this->assertMatch('/foo/article/7', 'DELETE', 'Article', 'destroy', ['id' => 7]);
	}

	public function test_resource(){
		$this->router->resource('article');
		$this->assertMatch('/article', 'GET', 'Article', 'index');
		$this->assertMatch('/article', 'POST', 'Article', 'create');
		$this->assertMatch('/article/7', 'GET', 'Article', 'show', ['id' => 7]);
		$this->assertMatch('/article/7', 'PUT', 'Article', 'update', ['id' => 7]);
		$this->assertMatch('/article/7', 'PATCH', 'Article', 'update', ['id' => 7]);
		$this->assertMatch('/article/7', 'DELETE', 'Article', 'destroy', ['id' => 7]);
	}

	public function test_resource_only_string(){
		$this->router->resource('article', ['only' => 'list']);
		$this->assertMatch('/article', 'GET', 'Article', 'index');
		$this->assertNotMatch('/article', 'POST');
		$this->assertNotMatch('/article/7', 'GET');
		$this->assertNotMatch('/article/7', 'PUT');
		$this->assertNotMatch('/article/7', 'PATCH');
		$this->assertNotMatch('/article/7', 'DELETE');
	}

	public function test_resource_only_array(){
		$this->router->resource('article', ['only' => ['list', 'destroy']]);
		$this->assertMatch('/article', 'GET', 'Article', 'index');
		$this->assertNotMatch('/article', 'POST');
		$this->assertNotMatch('/article/7', 'GET');
		$this->assertNotMatch('/article/7', 'PUT');
		$this->assertNotMatch('/article/7', 'PATCH');
		$this->assertMatch('/article/7', 'DELETE', 'Article', 'destroy', ['id' => 7]);
	}

	public function test_resource_except_string(){
		$this->router->resource('article', ['except' => 'list']);
		$this->assertNotMatch('/article', 'GET');
		$this->assertMatch('/article', 'POST', 'Article', 'create');
		$this->assertMatch('/article/7', 'GET', 'Article', 'show', ['id' => 7]);
		$this->assertMatch('/article/7', 'PUT', 'Article', 'update', ['id' => 7]);
		$this->assertMatch('/article/7', 'PATCH', 'Article', 'update', ['id' => 7]);
		$this->assertMatch('/article/7', 'DELETE', 'Article', 'destroy', ['id' => 7]);
	}

	public function test_resource_except_array(){
		$this->router->resource('article', ['except' => ['list', 'destroy']]);
		$this->assertNotMatch('/article', 'GET');
		$this->assertMatch('/article', 'POST', 'Article', 'create');
		$this->assertMatch('/article/7', 'GET', 'Article', 'show', ['id' => 7]);
		$this->assertMatch('/article/7', 'PUT', 'Article', 'update', ['id' => 7]);
		$this->assertMatch('/article/7', 'PATCH', 'Article', 'update', ['id' => 7]);
		$this->assertNotMatch('/article/7', 'DELETE');
	}

	public function test_resource_collection(){
		$this->router->resource('article', [], function($r){
			$r->collection(function($r){
				$r->delete('delete');
				$r->get('get');
				$r->patch('patch');
				$r->post('post');
				$r->put('put');
			});
		});
		$this->assertMatch('/article/delete', 'DELETE', 'Article', 'delete');
		$this->assertMatch('/article/get', 'GET', 'Article', 'get');
		$this->assertMatch('/article/patch', 'PATCH', 'Article', 'patch');
		$this->assertMatch('/article/post', 'POST', 'Article', 'post');
		$this->assertMatch('/article/put', 'PUT', 'Article', 'put');
	}

	public function test_resource_member(){
		$this->router->resource('article', [], function($r){
			$r->members(function($r){
				$r->delete('delete');
				$r->get('get');
				$r->patch('patch');
				$r->post('post');
				$r->put('put');
			});
		});
		$this->assertMatch('/article/1/delete', 'DELETE', 'Article', 'delete', ['id' => 1]);
		$this->assertMatch('/article/1/get', 'GET', 'Article', 'get', ['id' => 1]);
		$this->assertMatch('/article/1/patch', 'PATCH', 'Article', 'patch', ['id' => 1]);
		$this->assertMatch('/article/1/post', 'POST', 'Article', 'post', ['id' => 1]);
		$this->assertMatch('/article/1/put', 'PUT', 'Article', 'put', ['id' => 1]);
	}

	protected function assertMatch($url, $method, $controller, $action, array $args=[]){
		$match = $this->router->match($url, $method);
		$this->assertTrue((boolean)$match, "URL {$url} should match a route");
		$this->assertEquals($controller, $match->controller, 'Controller name');
		$this->assertEquals($action, $match->action, 'Action name');
		$this->assertEquals($args, $match->args, 'Arguments');
	}

	protected function assertNotMatch($url, $method){
		$match = $this->router->match($url, $method);
		$this->assertFalse((boolean)$match, "URL {$url} should not match a route");
	}
}

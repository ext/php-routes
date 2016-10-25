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
		$func();
	}

	public function test_multiple_args(){
		$func = $this->path_function('foo/:id/baz/:spam');
		$this->assertEquals('/foo/3/baz/4', $func(3, 4));
		$this->assertEquals('/foo/3/baz/4', $func(['id' => 3, 'spam' => 4]));
		$this->assertEquals('/foo/3/baz/4', $func((object)['id' => 3, 'spam' => 4]));
	}

	public function test_call_no_args(){
		$this->router->method('foo', 'GET', ['as' => 'foo']);
		$this->assertEquals('/foo', $this->router->foo_path());
	}

	public function test_call_positional_args(){
		$this->router->method('foo/:a/:b', 'GET', ['as' => 'foo']);
		$this->assertEquals('/foo/1/2', $this->router->foo_path(1,2));
	}

	public function test_call_named_args(){
		$this->router->method('foo/:a/:b', 'GET', ['as' => 'foo']);
		$this->assertEquals('/foo/2/1', $this->router->foo_path(['b' => 1, 'a' => 2]));
	}

	public function test_call_object_args(){
		$this->router->method('foo/:a/:b', 'GET', ['as' => 'foo']);
		$this->assertEquals('/foo/2/1', $this->router->foo_path((object)['b' => 1, 'a' => 2]));
	}

	public function test_call_missing_args(){
		$this->router->method('foo/:id', 'GET', ['as' => 'foo']);
		$this->expectException(\BadFunctionCallException::class);
		$this->router->foo_path();
	}

	public function test_call_missing(){
		$this->expectException(\PHPUnit_Framework_Error::class);
		$this->router->foo_path();
	}

    public function test_resource_call_no_args(){
        $this->router->resource('/article');
        $this->assertEquals('/article', $this->router->article_path());
        $this->assertEquals('/article', $this->router->create_article_path());
        $this->expectException(\BadFunctionCallException::class);
        $this->assertEquals('/article', $this->router->update_article_path());
    }

    public function test_resource_call_positional_args(){
        $this->router->resource('/article');
        $this->assertEquals('/article/1', $this->router->article_path(1));
        $this->assertEquals('/article/1', $this->router->update_article_path(1));
    }

    public function test_resource_call_named_args(){
        $this->router->resource('/article');
        $this->assertEquals('/article/1', $this->router->article_path(['id' => 1]));
        $this->assertEquals('/article/1', $this->router->update_article_path(['id' => 1]));
    }

    public function test_resource_call_object_args(){
        $this->router->resource('/article');
        $this->assertEquals('/article/1', $this->router->article_path((object)['id' => 1]));
        $this->assertEquals('/article/1', $this->router->update_article_path((object)['id' => 1]));
    }
}

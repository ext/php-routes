<?php

namespace RouterPath;

class RouterPathFunctionTest extends \PHPUnit_Framework_TestCase
{
    public $router;

    public function setUp()
    {
        $this->router = new \Sidvind\PHPRoutes\Router();
    }

    protected function pathFunction($pattern)
    {
        return $this->router->method($pattern, 'GET', ['as' => 'testPattern']);
    }

    public function testNoArgs()
    {
        $func = $this->pathFunction('foo');
        $this->assertEquals('/foo', $func());
    }

    public function testPositionalArg()
    {
        $func = $this->pathFunction('foo/:id');
        $this->assertEquals('/foo/7', $func(7));
    }

    public function testNamedArg()
    {
        $func = $this->pathFunction('foo/:id');
        $this->assertEquals('/foo/8', $func(['id' => 8]));
    }

    public function testObjectArg()
    {
        $func = $this->pathFunction('foo/:id');
        $this->assertEquals('/foo/9', $func((object)['id' => 9]));
    }

    public function testMissingArgs()
    {
        $func = $this->pathFunction('foo/:id');
        $this->expectException(\BadFunctionCallException::class);
        $func();
    }

    public function testMultipleArgs()
    {
        $func = $this->pathFunction('foo/:id/baz/:spam');
        $this->assertEquals('/foo/3/baz/4', $func(3, 4));
        $this->assertEquals('/foo/3/baz/4', $func(['id' => 3, 'spam' => 4]));
        $this->assertEquals('/foo/3/baz/4', $func((object)['id' => 3, 'spam' => 4]));
    }

    public function testCallNoArgs()
    {
        $this->router->method('foo', 'GET', ['as' => 'foo']);
        $this->assertEquals('/foo', $this->router->foo_path());
    }

    public function testCallPositionalArgs()
    {
        $this->router->method('foo/:a/:b', 'GET', ['as' => 'foo']);
        $this->assertEquals('/foo/1/2', $this->router->foo_path(1, 2));
    }

    public function testCallNamedArgs()
    {
        $this->router->method('foo/:a/:b', 'GET', ['as' => 'foo']);
        $this->assertEquals('/foo/2/1', $this->router->foo_path(['b' => 1, 'a' => 2]));
    }

    public function testCallObjectArgs()
    {
        $this->router->method('foo/:a/:b', 'GET', ['as' => 'foo']);
        $this->assertEquals('/foo/2/1', $this->router->foo_path((object)['b' => 1, 'a' => 2]));
    }

    public function testCallMissingArgs()
    {
        $this->router->method('foo/:id', 'GET', ['as' => 'foo']);
        $this->expectException(\BadFunctionCallException::class);
        $this->router->foo_path();
    }

    public function testCallMissing()
    {
        $this->expectException(\PHPUnit_Framework_Error::class);
        $this->router->foo_path();
    }

    public function testResourceCallNoArgs()
    {
        $this->router->resource('/article');
        $this->assertEquals('/article', $this->router->article_path());
        $this->assertEquals('/article', $this->router->create_article_path());
        $this->expectException(\BadFunctionCallException::class);
        $this->assertEquals('/article', $this->router->update_article_path());
    }

    public function testResourceCallPositionalArgs()
    {
        $this->router->resource('/article');
        $this->assertEquals('/article/1', $this->router->article_path(1));
        $this->assertEquals('/article/1', $this->router->update_article_path(1));
    }

    public function testResourceCallNamedArgs()
    {
        $this->router->resource('/article');
        $this->assertEquals('/article/1', $this->router->article_path(['id' => 1]));
        $this->assertEquals('/article/1', $this->router->update_article_path(['id' => 1]));
    }

    public function testResourceCallObjectArgs()
    {
        $this->router->resource('/article');
        $this->assertEquals('/article/1', $this->router->article_path((object)['id' => 1]));
        $this->assertEquals('/article/1', $this->router->update_article_path((object)['id' => 1]));
    }
}

<?php

namespace RouterDispatch;

class RouterDispatchFunctionTest extends \PHPUnit\Framework\TestCase
{
    private static $variable_formats;
    private static $default_format;

    public $router;

    public static function setUpBeforeClass(): void
    {
        /* save defaults */
        static::$variable_formats = \Sidvind\PHPRoutes\Router::$variable_formats;
        static::$default_format = \Sidvind\PHPRoutes\Router::$default_format;
    }

    public function setUp(): void
    {
        /* restore statics */
        \Sidvind\PHPRoutes\Router::$variable_formats = static::$variable_formats;
        \Sidvind\PHPRoutes\Router::$default_format = static::$default_format;
        $this->router = new \Sidvind\PHPRoutes\Router();
    }

    public function testSimple()
    {
        $this->router->addRoute('foo', 'GET', ['to' => 'Test#action']);
        $this->router->addRoute('bar', 'GET', []);
        $this->assertMatch('/foo', 'GET', 'Test', 'action');
        $this->assertMatch('/bar', 'GET', 'Index', 'bar');
    }

    public function testMethod()
    {
        $this->router->addRoute('foo', 'PATCH', ['to' => 'Test#action']);
        $this->assertMatch('/foo', 'PATCH', 'Test', 'action');
    }

    public function testMethodOnlyController()
    {
        $this->router->addRoute('foo', 'GET', ['to' => 'Test']);
        $this->assertMatch('/foo', 'GET', 'Test', 'foo');
    }

    public function testHead()
    {
        $this->router->addRoute('foo', 'GET', ['to' => 'Test#action']);
        $this->assertMatch('/foo', 'GET', 'Test', 'action');
        $this->assertMatch('/foo', 'HEAD', 'Test', 'action');
    }

    public function testWrongMethod()
    {
        $this->router->addRoute('foo', 'PATCH', ['to' => 'Test#action']);
        $match = $this->router->match('/foo', 'GET');
        $this->assertFalse((boolean)$match);
    }

    public function testArgs()
    {
        $this->router->addRoute('foo/:id', 'GET', ['to' => 'Test#action']);
        $this->assertMatch('/foo/7', 'GET', 'Test', 'action', ['id' => 7]);
    }

    public function testFormat()
    {
        $this->router->addRoute('foo', 'GET', ['to' => 'Test#action']);
        $match = $this->router->match('/foo.json', 'GET');
        $this->assertTrue((boolean)$match);
        $this->assertEquals('Test', $match->controller);
        $this->assertEquals('action', $match->action);
        $this->assertEquals([], $match->args);
        $this->assertEquals('application/json', $match->format);
    }

    public function testFormatMimetype()
    {
        $this->router->addRoute('foo', 'GET', ['to' => 'Test#action']);
        $this->assertEquals('text/html', $this->router->match('/foo.html', 'GET')->format);
        $this->assertEquals('application/json', $this->router->match('/foo.json', 'GET')->format);
        $this->assertEquals('text/markdown', $this->router->match('/foo.md', 'GET')->format);
        $this->assertEquals('text/plain', $this->router->match('/foo.txt', 'GET')->format);
        $this->assertEquals('application/xml', $this->router->match('/foo.xml', 'GET')->format);
        $this->assertEquals('image/svg+xml', $this->router->match('/foo.svg', 'GET')->format);
        $this->assertEquals('bar', $this->router->match('/foo.bar', 'GET')->format);
    }

    public function testScope()
    {
        $this->router->scope('foo', [], function ($r) {
            $r->addRoute('bar', 'GET', ['to' => 'Test#action']);
            $r->addRoute('baz', 'GET', []);
        });
        $this->assertMatch('/foo/bar', 'GET', 'Test', 'action');
        $this->assertMatch('/foo/baz', 'GET', 'Index', 'baz');
    }

    public function testScopeVariable()
    {
        $this->router->scope(':lang', [], function ($r) {
            $r->addRoute('bar', 'GET', ['to' => 'Test#action']);
            $r->addRoute('baz', 'GET', []);
        });
        $this->assertMatch('/en/bar', 'GET', 'Test', 'action', ['lang' => 'en']);
        $this->assertMatch('/en/baz', 'GET', 'Index', 'baz', ['lang' => 'en']);
    }

    public function testScopeNested()
    {
        $this->router->scope('foo', [], function ($r) {
            $r->scope('bar', [], function ($r) {
                $r->addRoute('spam', 'GET', ['to' => 'Test#action']);
                $r->addRoute('ham', 'GET', []);
            });
        });
        $this->assertMatch('/foo/bar/spam', 'GET', 'Test', 'action');
        $this->assertMatch('/foo/bar/ham', 'GET', 'Index', 'ham');
    }

    public function testScopeNestedTo()
    {
        $this->router->scope('foo', [], function ($r) {
            $r->scope('bar', ['to' => 'Bar'], function ($r) {
                $r->addRoute('baz', 'GET', []);
            });
        });
        $this->assertMatch('/foo/bar/baz', 'GET', 'Bar', 'baz');
    }

    public function testScopeResourceTo()
    {
        $this->router->scope('admin', ['to' => 'Admin'], function ($r) {
            $r->resource('post');
        });
        $this->assertMatch('/admin/post', 'GET', 'AdminPost', 'index');
    }

    public function testScopeResource()
    {
        $this->router->scope('foo', [], function ($r) {
            $r->resource('article');
        });
        $this->assertMatch('/foo/article', 'GET', 'Article', 'index');
        $this->assertMatch('/foo/article', 'POST', 'Article', 'create');
        $this->assertMatch('/foo/article/7', 'GET', 'Article', 'show', ['id' => 7]);
        $this->assertMatch('/foo/article/7', 'PUT', 'Article', 'update', ['id' => 7]);
        $this->assertMatch('/foo/article/7', 'PATCH', 'Article', 'update', ['id' => 7]);
        $this->assertMatch('/foo/article/7', 'DELETE', 'Article', 'destroy', ['id' => 7]);
    }

    public function testScopeResourceLeadingSlash()
    {
        $this->router->scope('foo', [], function ($r) {
            $r->resource('/article');
        });
        $this->assertMatch('/foo/article', 'GET', 'Article', 'index');
        $this->assertMatch('/foo/article', 'POST', 'Article', 'create');
        $this->assertMatch('/foo/article/7', 'GET', 'Article', 'show', ['id' => 7]);
        $this->assertMatch('/foo/article/7', 'PUT', 'Article', 'update', ['id' => 7]);
        $this->assertMatch('/foo/article/7', 'PATCH', 'Article', 'update', ['id' => 7]);
        $this->assertMatch('/foo/article/7', 'DELETE', 'Article', 'destroy', ['id' => 7]);
    }

    public function testResource()
    {
        $this->router->resource('article');
        $this->assertMatch('/article', 'GET', 'Article', 'index');
        $this->assertMatch('/article', 'POST', 'Article', 'create');
        $this->assertMatch('/article/7', 'GET', 'Article', 'show', ['id' => 7]);
        $this->assertMatch('/article/7', 'PUT', 'Article', 'update', ['id' => 7]);
        $this->assertMatch('/article/7', 'PATCH', 'Article', 'update', ['id' => 7]);
        $this->assertMatch('/article/7', 'DELETE', 'Article', 'destroy', ['id' => 7]);
    }

    public function testResourceOnlyString()
    {
        $this->router->resource('article', ['only' => 'list']);
        $this->assertMatch('/article', 'GET', 'Article', 'index');
        $this->assertNotMatch('/article', 'POST');
        $this->assertNotMatch('/article/7', 'GET');
        $this->assertNotMatch('/article/7', 'PUT');
        $this->assertNotMatch('/article/7', 'PATCH');
        $this->assertNotMatch('/article/7', 'DELETE');
    }

    public function testResourceOnlyArray()
    {
        $this->router->resource('article', ['only' => ['list', 'destroy']]);
        $this->assertMatch('/article', 'GET', 'Article', 'index');
        $this->assertNotMatch('/article', 'POST');
        $this->assertNotMatch('/article/7', 'GET');
        $this->assertNotMatch('/article/7', 'PUT');
        $this->assertNotMatch('/article/7', 'PATCH');
        $this->assertMatch('/article/7', 'DELETE', 'Article', 'destroy', ['id' => 7]);
    }

    public function testResourceExceptString()
    {
        $this->router->resource('article', ['except' => 'list']);
        $this->assertNotMatch('/article', 'GET');
        $this->assertMatch('/article', 'POST', 'Article', 'create');
        $this->assertMatch('/article/7', 'GET', 'Article', 'show', ['id' => 7]);
        $this->assertMatch('/article/7', 'PUT', 'Article', 'update', ['id' => 7]);
        $this->assertMatch('/article/7', 'PATCH', 'Article', 'update', ['id' => 7]);
        $this->assertMatch('/article/7', 'DELETE', 'Article', 'destroy', ['id' => 7]);
    }

    public function testResourceExceptArray()
    {
        $this->router->resource('article', ['except' => ['list', 'destroy']]);
        $this->assertNotMatch('/article', 'GET');
        $this->assertMatch('/article', 'POST', 'Article', 'create');
        $this->assertMatch('/article/7', 'GET', 'Article', 'show', ['id' => 7]);
        $this->assertMatch('/article/7', 'PUT', 'Article', 'update', ['id' => 7]);
        $this->assertMatch('/article/7', 'PATCH', 'Article', 'update', ['id' => 7]);
        $this->assertNotMatch('/article/7', 'DELETE');
    }

    public function testResourceCollection()
    {
        $this->router->resource('article', [], function ($r) {
            $r->collection(function ($r) {
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

    public function testResourceMember()
    {
        $this->router->resource('article', [], function ($r) {
            $r->members(function ($r) {
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

    public function testContext()
    {
        $root = $this->router->context();
        $root->delete('/delete');
        $root->get('/get');
        $root->patch('/patch');
        $root->post('/post');
        $root->put('/put');
        $this->assertMatch('/delete', 'DELETE', 'Index', 'delete');
        $this->assertMatch('/get', 'GET', 'Index', 'get');
        $this->assertMatch('/patch', 'PATCH', 'Index', 'patch');
        $this->assertMatch('/post', 'POST', 'Index', 'post');
        $this->assertMatch('/put', 'PUT', 'Index', 'put');
    }

    public function testVariableFormat()
    {
        $this->router->addRoute(':foo', 'GET', ['to' => '#foo', 'foo_format' => '\d+']);
        $this->assertTrue((boolean)$this->router->match('/1234', 'GET'));
        $this->assertFalse((boolean)$this->router->match('/asdf', 'GET'));
    }

    public function testVariableFormatStaticDefault()
    {
        \Sidvind\PHPRoutes\Router::$variable_formats['foo'] = '\d+';
        $this->router->addRoute(':foo', 'GET', ['to' => '#foo']);
        $this->assertTrue((boolean)$this->router->match('/1234', 'GET'));
        $this->assertFalse((boolean)$this->router->match('/asdf', 'GET'));
    }

    public function testVariableFormatGlobalStaticDefault()
    {
        \Sidvind\PHPRoutes\Router::$default_format = '\d+';
        $this->router->addRoute(':foo', 'GET', ['to' => '#foo']);
        $this->assertTrue((boolean)$this->router->match('/1234', 'GET'));
        $this->assertFalse((boolean)$this->router->match('/asdf', 'GET'));
    }

    public function testArbitraryOptions()
    {
        $this->router->addRoute('foo/:id', 'GET', ['to' => '#foo', 'id_format' => '\d+', 'foo' => 'bar']);
        $match = $this->router->match('/foo/12', 'GET');
        $this->assertTrue((boolean)$match, "URL should match a route");
        $this->assertEquals(['foo' => 'bar'], $match->options, "Match options should be set");
    }

    protected function assertMatch($url, $method, $controller, $action, array $args = [])
    {
        $match = $this->router->match($url, $method);
        $this->assertTrue((boolean)$match, "URL {$url} should match a route");
        $this->assertEquals($controller, $match->controller, 'Controller name');
        $this->assertEquals($action, $match->action, 'Action name');
        $this->assertEquals($args, $match->args, 'Arguments');
    }

    protected function assertNotMatch($url, $method)
    {
        $match = $this->router->match($url, $method);
        $this->assertFalse((boolean)$match, "URL {$url} should not match a route");
    }
}

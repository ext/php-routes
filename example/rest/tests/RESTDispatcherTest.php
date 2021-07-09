<?php

namespace Example\REST;

class RESTDispatcherTest extends \PHPUnit\Framework\TestCase
{
    protected $status;

    public function setUp(): void
    {
        $this->status = -1;
        $this->isAuthenticated = false;
        $this->router = $this->getMockBuilder(\Example\REST\Dispatcher::class)
                             ->setMethods([
                                 'httpResponseCode',
                             ])
                             ->getMock();

        /* mock httpResponseCode call */
        $this->router->method('httpResponseCode')->will($this->returnCallback(function ($code) {
            $this->status = $code;
        }));
    }

    public function testRoutes()
    {
        $this->assertStringEqualsFile(__DIR__ . "/routes.txt", $this->router->formatRoutes(true));
    }

    public function testREST404()
    {
        $result = $this->router->dispatch('/missing', 'GET');
        $this->assertEquals(404, $this->status);
        $this->assertEquals(null, $result);
    }

    public function testRESTVerbGET()
    {
        $result = $this->router->dispatch('/foo', 'GET');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('get /foo', $result);
    }

    public function testRESTVerbPOST()
    {
        $result = $this->router->dispatch('/foo', 'POST');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('post /foo', $result);
    }

    public function testRESTVerbPATCH()
    {
        $result = $this->router->dispatch('/foo/1', 'PATCH');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('patch /foo/1', $result);
    }

    public function testRESTVerbPUT()
    {
        $result = $this->router->dispatch('/foo/1', 'PUT');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('put /foo/1', $result);
    }

    public function testRESTVerbDELETE()
    {
        $result = $this->router->dispatch('/foo/1', 'DELETE');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('delete /foo/1', $result);
    }

    public function testRESTVerbOPTIONS()
    {
        $result = $this->router->dispatch('/foo', 'OPTIONS');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('options /foo', $result);
    }

    public function testRESTScopeGET()
    {
        $result = $this->router->dispatch('/api/v1/bar', 'GET');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('get /api/v1/bar', $result);
    }

    public function testRESTScopePOST()
    {
        $result = $this->router->dispatch('/api/v1/bar', 'POST');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('post /api/v1/bar', $result);
    }

    public function testRESTScopePATCH()
    {
        $result = $this->router->dispatch('/api/v1/bar/1', 'PATCH');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('patch /api/v1/bar/1', $result);
    }

    public function testRESTScopePUT()
    {
        $result = $this->router->dispatch('/api/v1/bar/1', 'PUT');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('put /api/v1/bar/1', $result);
    }

    public function testRESTScopeDELETE()
    {
        $result = $this->router->dispatch('/api/v1/bar/1', 'DELETE');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('delete /api/v1/bar/1', $result);
    }

    public function testRESTResourceGET()
    {
        $result = $this->router->dispatch('/baz', 'GET');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('get /baz', $result);
    }

    public function testRESTResourcePOST()
    {
        $result = $this->router->dispatch('/baz', 'POST');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('post /baz', $result);
    }

    public function testRESTResourcePATCH()
    {
        $result = $this->router->dispatch('/baz/1', 'PATCH');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('patch /baz/1', $result);
    }

    public function testRESTResourcePUT()
    {
        $result = $this->router->dispatch('/baz/1', 'PUT');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('put /baz/1', $result);
    }

    public function testRESTResourceDELETE()
    {
        $result = $this->router->dispatch('/baz/1', 'DELETE');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('delete /baz/1', $result);
    }
}

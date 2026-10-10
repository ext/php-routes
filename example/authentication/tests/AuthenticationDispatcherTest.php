<?php

namespace Example\Authentication;

class AuthenticationDispatcherTest extends \PHPUnit\Framework\TestCase
{
    protected $status;
    protected $isAuthenticated;
    protected $router;

    public function setUp(): void
    {
        $this->status = -1;
        $this->isAuthenticated = false;
        $this->router = $this->getStubBuilder(\Example\Authentication\Dispatcher::class)
                             ->onlyMethods([
                                 'isAuthenticated',
                                 'httpResponseCode',
                             ])
                             ->getStub();

        /* mock isAuthenticated call */
        $this->router->method('isAuthenticated')->willReturnCallback(function () {
            return $this->isAuthenticated;
        });

        /* mock httpResponseCode call */
        $this->router->method('httpResponseCode')->willReturnCallback(function ($code) {
            $this->status = $code;
        });
    }

    public function testRoutes()
    {
        $this->assertStringEqualsFile(__DIR__ . "/routes.txt", $this->router->formatRoutes(true));
    }

    public function testPublic200()
    {
        $result = $this->router->dispatch('/public/foo', 'GET');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('ok', $result);
    }

    public function testPrivate200()
    {
        $this->isAuthenticated = true;
        $result = $this->router->dispatch('/private/foo', 'GET');
        $this->assertEquals(200, $this->status);
        $this->assertEquals('ok', $result);
    }

    public function test403()
    {
        $result = $this->router->dispatch('/private/foo', 'GET');
        $this->assertEquals(403, $this->status);
        $this->assertEquals(null, $result);
    }

    public function test404()
    {
        $result = $this->router->dispatch('/missing', 'GET');
        $this->assertEquals(404, $this->status);
        $this->assertEquals(null, $result);
    }
}

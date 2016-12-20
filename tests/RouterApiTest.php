<?php

namespace RouterApi;

class RouterApiFunctionTest extends \PHPUnit_Framework_TestCase
{
    public $router;

    public function setUp()
    {
        $this->router = new \Testing\TestRouter();
    }

    public function testClear()
    {
        $this->assertEquals(0, $this->router->numRoutes());
        $this->router->method('foo', 'GET', []);
        $this->assertEquals(1, $this->router->numRoutes());
        $this->router->clear();
        $this->assertEquals(0, $this->router->numRoutes());
    }

    public function testFormatRoutes()
    {
        $this->router->method('foo', 'GET', []);
        $this->assertStringEqualsFile(__DIR__ . "/formatted.txt", $this->router->formatRoutes());
    }

    public function testFormatRoutesVerbose()
    {
        $this->router->method('foo', 'GET', []);
        $this->assertStringEqualsFile(__DIR__ . "/formatted-verbose.txt", $this->router->formatRoutes(true));
    }

    public function testFormatRoutesEmpty()
    {
        $this->assertEquals('', $this->router->formatRoutes());
    }

    public function testPrintRoutesEmpty()
    {
        $this->router->method('foo', 'GET', []);
        ob_start();
        $this->router->printRoutes();
        $string = ob_get_contents();
        ob_end_clean();
        $this->assertStringEqualsFile(__DIR__ . "/formatted.txt", $this->router->formatRoutes());
    }
}

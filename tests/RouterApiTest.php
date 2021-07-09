<?php

namespace RouterApi;

class RouterApiFunctionTest extends \PHPUnit\Framework\TestCase
{
    public $router;

    public function setUp(): void
    {
        $this->router = new \Testing\TestRouter();
    }

    public function testClear()
    {
        $this->assertEquals(0, $this->router->numRoutes());
        $this->router->addRoute('foo', 'GET', []);
        $this->assertEquals(1, $this->router->numRoutes());
        $this->router->clear();
        $this->assertEquals(0, $this->router->numRoutes());
    }

    public function testFormatRoutes()
    {
        $this->router->addRoute('foo', 'GET', []);
        $this->assertStringEqualsFile(__DIR__ . "/formatted.txt", $this->router->formatRoutes());
    }

    public function testFormatRoutesVerbose()
    {
        $this->router->addRoute('foo', 'GET', []);
        $this->assertStringEqualsFile(__DIR__ . "/formatted-verbose.txt", $this->router->formatRoutes(true));
    }

    public function testFormatRoutesEmpty()
    {
        $this->assertEquals('', $this->router->formatRoutes());
    }

    public function testPrintRoutesEmpty()
    {
        $this->router->addRoute('foo', 'GET', []);
        ob_start();
        $this->router->printRoutes();
        $string = ob_get_contents();
        ob_end_clean();
        $this->assertStringEqualsFile(__DIR__ . "/formatted.txt", $this->router->formatRoutes());
    }

    public function testContructorFilename()
    {
        $this->router = new \Testing\TestRouter(__DIR__ . '/routes.php');
        $this->assertStringEqualsFile(__DIR__ . "/routes.txt", $this->router->formatRoutes());
    }
}

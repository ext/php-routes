<?php

namespace RouterTo;

class RouterToTest extends \PHPUnit\Framework\TestCase
{
    public $router;

    public function setUp(): void
    {
        $this->router = new \Testing\TestRouter();
    }

    public function testBlank()
    {
        $this->assertEquals(['Index', 'index'], $this->router->parseTo(''));
    }

    public function testOnlyHash()
    {
        $this->assertEquals(['Index', 'index'], $this->router->parseTo('#'));
    }

    public function testOnlyController()
    {
        $this->assertEquals(['Foo', 'index'], $this->router->parseTo('Foo'));
    }

    public function testOnlyControllerWithAction()
    {
        $this->assertEquals(['Foo', 'bar'], $this->router->parseTo('Foo', 'bar'));
    }

    public function testOnlyAction()
    {
        $this->assertEquals(['Index', 'foo'], $this->router->parseTo('#foo'));
    }

    public function testControllerAction()
    {
        $this->assertEquals(['Foo', 'bar'], $this->router->parseTo('Foo#bar'));
    }

    public function testMalformed2()
    {
        $this->expectException(\BadFunctionCallException::class);
        $this->assertEquals(['Index', 'index'], $this->router->parseTo('foo##bar'));
    }
}

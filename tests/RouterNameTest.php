<?php

namespace RouterName;

class RouterNameTest extends \PHPUnit\Framework\TestCase
{
    public $router;

    public function setUp(): void
    {
        $this->router = new \Testing\TestRouter();
    }

    public function testBlank()
    {
        $this->assertEquals('index', $this->router->testName(''));
    }

    public function testName()
    {
        $this->assertEquals('foo', $this->router->testName('foo'));
    }

    public function testLeadingSlash()
    {
        $this->assertEquals('foo', $this->router->testName('/foo'));
    }

    public function testSeparator()
    {
        $this->assertEquals('foo_bar', $this->router->testName('foo/bar'));
    }

    public function testVariable()
    {
        $this->assertEquals('foo', $this->router->testName('foo/:id'));
    }

    public function testVariableSeparator()
    {
        $this->assertEquals('foo_baz', $this->router->testName('foo/:id/baz'));
    }
}

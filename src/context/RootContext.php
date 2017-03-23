<?php

namespace Sidvind\PHPRoutes;

class RootContext
{
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function get($pattern, array $options = [])
    {
        $this->router->addRoute($pattern, 'GET', $options);
    }

    public function post($pattern, array $options = [])
    {
        $this->router->addRoute($pattern, 'POST', $options);
    }

    public function patch($pattern, array $options = [])
    {
        $this->router->addRoute($pattern, 'PATCH', $options);
    }

    public function put($pattern, array $options = [])
    {
        $this->router->addRoute($pattern, 'PUT', $options);
    }

    public function delete($pattern, array $options = [])
    {
        $this->router->addRoute($pattern, 'DELETE', $options);
    }

    public function resource($pattern, array $options = [], $callback = false)
    {
        $this->router->resource($pattern, $options, $callback);
    }

    public function scope($pattern, array $options, $callback)
    {
        $this->router->scope($pattern, $options, $callback);
    }
}

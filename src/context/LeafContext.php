<?php

namespace Sidvind\PHPRoutes;

class LeafContext
{
    protected $namespace;
    protected $options;
    protected $router;

    public function __construct($namespace, array $options, $router)
    {
        $this->namespace = $namespace;
        $this->options = $options;
        $this->router = $router;
    }

    public function get($pattern, array $options = [])
    {
        $this->addRoute($pattern, 'GET', $options);
    }

    public function post($pattern, array $options = [])
    {
        $this->addRoute($pattern, 'POST', $options);
    }

    public function patch($pattern, array $options = [])
    {
        $this->addRoute($pattern, 'PATCH', $options);
    }

    public function put($pattern, array $options = [])
    {
        $this->addRoute($pattern, 'PUT', $options);
    }

    public function delete($pattern, array $options = [])
    {
        $this->addRoute($pattern, 'DELETE', $options);
    }

    public function addRoute($pattern, $method, array $options = [])
    {
        $pattern = ltrim($pattern, '/');
        $default_to = $this->options['to'] . '#' . Utils::actionname($pattern);
        $options = array_merge($this->options, ['to' => $default_to], $options);
        $options = $this->fillTo($options);
        $this->router->addRoute("{$this->namespace}/{$pattern}", $method, $options);
    }

    /**
     * If 'to' is '#foo' it fills the controller from context.
     */
    protected function fillTo($options)
    {
        if ($options['to'][0] == '#') {
            $options['to'] = $this->options['to'] . $options['to'];
        }
        return $options;
    }
}

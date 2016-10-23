<?php

namespace Sidvind\PHPRoutes;

class ScopeContext extends LeafContext
{
    public function resource($pattern, array $options = [], $callback = false)
    {
        $prefix = $this->options['to'] ?: '';
        $defaults = [
            'to' => $prefix . Utils::classname($pattern),
        ];
        $resource_options = array_merge($this->options, $defaults, $options);
        $this->router->resource("{$this->namespace}/{$pattern}", $resource_options, $callback);
    }

    public function scope($pattern, array $options = [], $callback = false)
    {
        $this->router->scope("{$this->namespace}/$pattern", array_merge($this->options, $options), $callback);
    }
}

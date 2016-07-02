<?php

namespace Sidvind\PHPRoutes;

class RouterScopeContext extends RouterLeafContext {
	public function resource($pattern, array $options=[], $callback=false){
		$this->router->resource("{$this->namespace}/{$pattern}", array_merge($this->options, ['to' => $this->options['to'] . Utils::classname($pattern)], $options), $callback);
	}

	public function scope($pattern, array $options=[], $callback=false){
		$this->router->scope("{$this->namespace}/$pattern", array_merge($this->options, ['to' => $this->options['to'] . Utils::classname($pattern)], $options), $callback);
	}
};

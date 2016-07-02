<?php

namespace Sidvind\PHPRoutes;

class ResourceContext {
	protected $namespace;
	protected $options;
	protected $router;

	public function __construct($namespace, array $options, $router){
		$this->namespace = $namespace;
		$this->options = $options;
		$this->router = $router;
	}

	public function members($callback){
		$context = new LeafContext("{$this->namespace}/:id", $this->options, $this->router);
		$callback($context);
	}

	public function collection($callback){
		$context = new LeafContext("{$this->namespace}", $this->options, $this->router);
		$callback($context);
	}
}

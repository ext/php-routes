<?php

namespace Sidvind\PHPRoutes;

class RouterLeafContext {
	protected $namespace;
	protected $options;
	protected $router;

	public function __construct($namespace, array $options, $router){
		$this->namespace = $namespace;
		$this->options = $options;
		$this->router = $router;
	}

	public function get($pattern, array $options=[]){
		$this->method($pattern, 'GET', $options);
	}

	public function post($pattern, array $options=[]){
		$this->method($pattern, 'POST', $options);
	}

	public function put($pattern, array $options=[]){
		$this->method($pattern, 'PUT', $options);
	}

	public function delete($pattern, array $options=[]){
		$this->method($pattern, 'DELETE', $options);
	}

	public function method($pattern, $method, array $options=[]){
		$pattern = ltrim($pattern, '/');
		$default_to = $this->options['to'] . '#' . prouter_actionname($pattern);
		$options = array_merge($this->options, ['to' => $default_to], $options);
		$options = $this->fill_to($options);
		$this->router->method("{$this->namespace}/{$pattern}", $method, $options);
	}

	/**
	 * If 'to' is '#foo' it fills the controller from context.
	 */
	protected function fill_to($options){
		if ( $options['to'][0] == '#' ){
			$options['to'] = $this->options['to'] . $options['to'];
		}
		return $options;
	}
};

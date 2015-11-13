<?php

namespace Sidvind\PHPRoutes;

class RouterMatch {
	public $controller;        /* controller name (via 'to' route parameter) */
	public $action;            /* controller action (via 'to' route parameter) */
	public $args;              /* assoc array with named arguments */
	public $format;            /* requested format or false for any */

	public function __construct($controller, $action, $args, $format){
		$this->controller = $controller;
		$this->action = $action;
		$this->args = $args;
		$this->format = $format;
	}
}

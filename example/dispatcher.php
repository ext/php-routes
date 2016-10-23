<?php

namespace ExampleApplication;

class MyDispatcher extends Sidvind\PHPRoutes\Router {
	public function dispatch($url, $method){
		if ( $match = $this->match($url, $method) ){
			$class = "\\ExampleApplication\\{$match->controller}Controller";
			$controller = new $class();
			return call_user_func_array([$controller, $match->action], $match->args);
		} else {
			echo "no match\n";
			return;
		}
	}
}

<?php

require('proutes.php');

class IndexController {
	public function index(){
		echo "index\n";
	}

	public function about(){
		echo "about\n";
	}
};

class PostController {
	public function show($id){
		echo "show post $id\n";
	}
}

class MyRouter extends Prouter {
	public function dispatch($url, $method){
		list($controller, $action, $args) = $this->match($url, $method);	
		if ( !$controller ){
			echo "no match\n";
			return;
		}
		
		$class = "{$controller}Controller";
		$controller = new $class();
		return call_user_func_array([$controller, $action], $args);
	}
}

$router = new MyRouter('routes.php');
$router->dispatch(isset($argv[1]) ? $argv[1] : '/', 'GET');	

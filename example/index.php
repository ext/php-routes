<?php

require __DIR__ . '/../vendor/autoload.php';

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

class MyRouter extends Sidvind\PHPRoutes\Router {
	public function dispatch($url, $method){
		if ( $match = $this->match($url, $method) ){
			$class = "{$match->controller}Controller";
			$controller = new $class();
			return call_user_func_array([$controller, $match->action], $match->args);
		} else {
			echo "no match\n";
			return;
		}
	}
}

$router = new MyRouter(__DIR__ . '/routes.php');
$router->dispatch(isset($argv[1]) ? $argv[1] : '/', 'GET');

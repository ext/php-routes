<?php

require_once('proutes.php');

class TestProuter extends Prouter {
	function test_match($pattern, $method, $expected_controller, $expected_action, array $expected_args=[]){
		echo "  - Testing $method '$pattern' .. ";
		list($actual_controller, $actual_action, $args) = $this->match($pattern, $method);

		if ( $actual_controller != $expected_controller ){
			echo "failed (controller)\n      Expected:   $expected_controller\n      Actual:     $actual_controller\n";
			return;
		}

		if ( $actual_action != $expected_action ){
			echo "failed (action)\n      Expected:   $expected_action\n      Actual:     $actual_action\n";
			return;
		}

		/** @todo test arguments */

		echo "ok\n";
	}
};

$router = new TestProuter();

echo "Testing dispatching\n";
$router->clear();
$router->method('foo', 'GET', ['to' => 'Index']);
$router->use_namespace('namespace', [], function($x){
	$x->method('bar', 'GET', ['to' => 'Test']);
});
$router->test_match('/foo', 'GET', 'Index', 'foo');
$router->test_match('/namespace/bar', 'GET', 'Test', 'namespace_bar');

$router->clear();
$router->use_namespace(':lang', [], function($x){
	$x->get('page', ['to' => 'Test#show']);
});
$router->test_match('/en/page', 'GET', 'Test', 'show', ['lang' => 'en']);

$router->clear();
$router->resource('article');
$router->test_match('/article', 'GET', 'Article', 'index');
$router->test_match('/article', 'POST', 'Article', 'create');
$router->test_match('/article/7', 'GET', 'Article', 'show');
$router->test_match('/article/7', 'PUT', 'Article', 'update');
$router->test_match('/article/7', 'PATCH', 'Article', 'update');
$router->test_match('/article/7', 'DELETE', 'Article', 'destroy');

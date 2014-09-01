<?php

require_once('proutes.php');

class TestProuter extends Prouter {
	public function test_to($str, $controller, $action){
		echo " - Testing '$str' .. ";
		$actual = $this->parse_to($str);
		$expected = array($controller, $action);
		if ( $actual != $expected ){
			echo "failed\n     Expected:   $controller::$action\n     Actual:     {$actual[0]}::{$actual[1]}\n";
		} else {
			echo "ok\n";
		}
	}

	public function test_pattern($pattern, $controller, $action){
		$this->clear();

		echo " - Testing '$pattern' .. ";
		$this->method($pattern, 'GET', []);
		list(,,, $actual[0], $actual[1]) = $this->patterns[0];
		$expected = array($controller, $action);
		if ( $actual != $expected ){
			echo "failed\n     Expected:   $controller::$action\n     Actual:     {$actual[0]}::{$actual[1]}\n";
		} else {
			echo "ok\n";
		}		
	}

	function test_path($pattern, $expected, $named, $positional){
		global $test_pattern_path;
		$this->clear();

		$this->method($pattern, 'GET', ['as' => 'test_pattern']);
		echo "  - Testing '$pattern' .. ";
		if ( !(isset($test_pattern_path) && is_callable($test_pattern_path)) ){
			echo "failed\n      No function defined\n";
			return;
		}

		$actual = $test_pattern_path($named);
		if ( $actual != $expected ){
			echo "failed (named args)\n      Expected:   $expected\n      Actual:     $actual\n";
			return;
		}

		$actual = call_user_func_array($test_pattern_path, $positional);
		if ( $actual != $expected ){
			echo "failed (positional)\n      Expected:   $expected\n      Actual:     $actual\n";
			return;
		}

		echo "ok\n";
	}

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

echo "Testing parsing 'to'\n";
$router->test_to('', 'Index', 'index');
$router->test_to('#', 'Index', 'index');
$router->test_to('Foo', 'Foo', 'index');
$router->test_to('#foo', 'Index', 'foo');
$router->test_to('Foo#bar', 'Foo', 'bar');
echo "\n";

echo "Testing pattern\n";
$router->test_pattern('', 'Index', 'index');
$router->test_pattern('foo', 'Index', 'foo');
$router->test_pattern('foo', 'Index', 'foo');
$router->test_pattern('/foo', 'Index', 'foo');
$router->test_pattern('foo/bar', 'Index', 'foo_bar');
$router->test_pattern('foo/:id', 'Index', 'foo');
$router->test_pattern('foo/:id/baz', 'Index', 'foo_baz');
echo "\n";

echo "Testing path functions\n";
$router->test_path('foo', '/foo', [], []);
$router->test_path('foo/:id', '/foo/7', ['id' => 7], [7]);
$router->test_path('foo/:id/baz/:spam', '/foo/7/baz/ham', ['id' => 7, 'spam' => 'ham'], [7, 'ham']);
echo "\n";

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

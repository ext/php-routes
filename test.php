<?php

require_once('proutes.php');
$router = new TestProuter();

echo "Testing dispatching\n";
$router->clear();
$router->use_namespace('namespace', [], function($x){
	$x->method('bar', 'GET', ['to' => 'Test']);
});
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

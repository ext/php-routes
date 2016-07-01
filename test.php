<?php

require_once('proutes.php');
$router = new TestProuter();

echo "Testing dispatching\n";

$router->clear();
$router->resource('article');
$router->test_match('/article', 'GET', 'Article', 'index');
$router->test_match('/article', 'POST', 'Article', 'create');
$router->test_match('/article/7', 'GET', 'Article', 'show');
$router->test_match('/article/7', 'PUT', 'Article', 'update');
$router->test_match('/article/7', 'PATCH', 'Article', 'update');
$router->test_match('/article/7', 'DELETE', 'Article', 'destroy');

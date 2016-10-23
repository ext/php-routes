<?php

require __DIR__ . '/../vendor/autoload.php';

$router = new \ExampleApplication\MyRouter(__DIR__ . '/routes.php');
$router->dispatch(isset($argv[1]) ? $argv[1] : '/', 'GET');

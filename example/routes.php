<?php

$get('');
$get('about');
$resource('post');

//$get('contact', ['to' => '#contact_form']);
//$post('api', ['to' => 'ApiController#action']);

//$get('a');
//$get('b', ['to' => 'Test']);
//$get('c', ['to' => 'Test#c']);


$scope(':event', ['event_format' => '[a-z0-9]+'], function ($r) {
    $r->get('a');
    $r->resource('user', ['only' => ['show']]);
    $r->scope('admin', ['to' => 'Admin'], function ($r) {
        $r->resource('event', ['id_format' => '[0-9]+']);
        $r->resource('order');
    });
});

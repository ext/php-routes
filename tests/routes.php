<?php

$get('/get');
$post('/post');
$put('/put');
$delete('/delete');
$resource('/resource');
$scope('/scope', [], function ($r) {
    $r->get('foo');
});

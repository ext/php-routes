<?php

static::$default_format = '\d+';

/* rest verbs */
$get('/foo', ['to' => '#list']);
$post('/foo', ['to' => '#create']);
$patch('/foo/:id', ['to' => '#update']);
$put('/foo/:id', ['to' => '#replace']);
$delete('/foo/:id', ['to' => '#remove']);
$addRoute('/foo', 'OPTIONS');

/* scoped */
$scope('/api/v1/', [], function ($r) {
    $r->get('/bar', ['to' => '#list']);
    $r->post('/bar', ['to' => '#create']);
    $r->patch('/bar/:id', ['to' => '#update']);
    $r->put('/bar/:id', ['to' => '#replace']);
    $r->delete('/bar/:id', ['to' => '#remove']);
});

/* as resource */
$resource('baz', ['except' => 'new']);

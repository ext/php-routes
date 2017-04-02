<?php

/* A public area where no authentication is required. */
$scope('/public', ['need_auth' => false], function ($r) {
    $r->get('/foo');
});

/* A private area where users must be logged in. */
$scope('/private', ['need_auth' => true], function ($r) {
    $r->get('/foo');
});

========
Overview
========

``php-routes`` is a simple URL to method matcher. It is designed to be a
extensionable lightweight router for MVC-like projects.

What it does:

* Match URLs.

What it does not:

* Instantiate classes.
* Content negotiation.
* Authentication and similar filters.

However, the dispatcher makes it easy to implement all of above.

The design goals are:

* Lightweight
* Few or no dependencies
* Easy to write tests for

Requirements
============

#. PHP 5.6 or later

Installation
============

.. code-block:: bash

    composer require sidvind/php-routes

Usage
=====

Put routes in a separate file, e.g. ``routes.php``:

.. code-block:: php

    <?php
    /* basic routes */
    $get('foo', ['to' => 'MyController#foo']);
    $post('bar/:id/baz', ['to' => 'MyController#update']); /* use :var for variables */
    
    /* automatically setup RESTful routes */
    $resource('article', [], function($r){
      $r->members(function($r){
        $r->patch('frobnicate'); /* maps to PATCH /article/:id/frobnicate */
      });
      $r->collection(function($r){
        $r->patch('twiddle'); /* maps to PATCH /article/twiddle */
      });
    });
    
    /* scoping */
    $scope(':lang', [], function($r){
      $r->get('barney'); /* maps to GET /:lang/barney */
    });

Create a dispatcher:

.. code-block:: php

    <?php
    class Dispatcher extends Sidvind\PHPRoutes\Router {
       public function dispatch($url, $method){
         if ( $match = $this->match($url, $method) ){
           $class = "{$match->controller}Controller";
           $controller = new $class();
           return call_user_func_array([$controller, $match->action], $match->args);
         } else {
           /* 404 */
         }
       }
    }
    $router = new Dispatcher('routes.php');
    $router->dispatch($url, $method);

To preview/debug routes use ``bin/php-routes``:

.. code::

    # bin/php-routes routes.php
            GET    /foo             MyController#foo      #^/foo(?P<format>\.\w+)?$#
            POST   /bar/:id/baz     MyController#update   #^/bar/(?P<id>[A-Za-z0-9\-_\.]+)/baz(?P<format>\.\w+)?$#
    article GET    /article         Article#list          #^/article(?P<format>\.\w+)?$#

    # bin/php-routes routes.php get /foo
    Controller: MyController
    Action: foo
    Format:
    Arguments:
    []

    # bin/php-routes routes.php get /bar
    bin/php-routes: url doesn't match any route.

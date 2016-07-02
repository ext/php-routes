PHP-routes
==========

[![Build Status](https://travis-ci.org/ext/php-routes.svg?branch=master)](https://travis-ci.org/ext/php-routes) [![Coverage Status](https://coveralls.io/repos/github/ext/php-routes/badge.svg?branch=master)](https://coveralls.io/github/ext/php-routes?branch=master)

Routing for MVC-ish PHP projects.

    composer require sidvind/php-routes

Example
-------

Put routes in a separate file, e.g. `routes.php`:

```php
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
```

Create a dispatcher:

```php
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
```

To preview/debug routes use `bin/php-routes`:

```
# bin/php-routes routes.php
        GET    /foo             MyController#foo      #^/foo(?P<format>\.\w+)?$#
        POST   /bar/:id/baz     MyController#update   #^/bar/(?P<id>[A-Za-z0-9\-_\.]+)/baz(?P<format>\.\w+)?$#
article GET    /article         Article#list          #^/article(?P<format>\.\w+)?$#
...
# bin/php-routes routes.php get /foo
Controller: MyController
Action: foo
Format:
Arguments:
[]
# bin/php-routes routes.php get /bar
bin/php-routes: url doesn't match any route.
```

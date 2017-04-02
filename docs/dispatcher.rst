Dispatcher
==========

.. role:: php(code)
   :language: php

Sample dispatcher:

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

Matches
-------

When calling :php:`$this->match($url, $method)` a :doc:`classes/routermatch` or ``null``
is returned. The ``RouterMatch`` instance contains all data about the matched route:

- Controller
- Action
- Variables
- Extension (format)
- User-defined options (any extra options passed when creating the route)

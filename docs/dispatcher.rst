Dispatcher
==========

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

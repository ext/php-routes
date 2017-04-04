<?php

namespace Example\REST;

class Dispatcher extends \Sidvind\PHPRoutes\Router
{
    public function __construct()
    {
        parent::__construct(__DIR__ . '/routes.php');
    }

    public function dispatch($url, $method)
    {
        $match = $this->match($url, $method);

        /* Respond with "404 Not Found" if no route matches */
        if (!$match) {
            $this->httpResponseCode(404);
            return null;
        }

        /* In this example the body is hardcoded but a real dispatch would do
         * some real work here. */
        $this->httpResponseCode(200);
        return strtolower("$method $url");
    }

    // The rest of the methods are present for ease of testing
    //
    // @codeCoverageIgnoreStart

    protected function httpResponseCode($code)
    {
        http_response_code($code);
    }

    // @codeCoverageIgnoreEnd
}

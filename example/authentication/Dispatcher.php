<?php

namespace Example\Authentication;

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

        /* Respond with "403 Forbidden" if authentication is required but user
         * is not logged in */
        if ($match->options['need_auth'] && !$this->isAuthenticated()) {
            $this->httpResponseCode(403);
            return null;
        }

        /* In this example the body is hardcoded but a real dispatch would do
         * some real work here. */
        $this->httpResponseCode(200);
        return 'ok';
    }

    // The rest of the methods are present for ease of testing
    //
    // @codeCoverageIgnoreStart

    protected function isAuthenticated()
    {
        return false;
    }

    protected function httpResponseCode($code)
    {
        http_response_code($code);
    }

    // @codeCoverageIgnoreEnd
}

<?php

namespace Sidvind\PHPRoutes;

class RouterMatch
{
    /**
     * Controller name (via ``to`` route parameter)
     */
    public $controller;

    /**
     * Controller action (via ``to`` route parameter)
     */
    public $action;

    /**
     * Associative array with variables from matched pattern.
     */
    public $args;

    /**
     * Requested format or false if no extension was specified.
     */
    public $format;

    /**
     * User-defined options set on the route.
     *
     * In practice this is all the options passed to ``addRroute`` except:
     *
     * - ``_format``
     * - ``as``
     * - ``except``
     * - ``only``
     * - ``to``
     */
    public $options;

    public function __construct($controller, $action, $args, $format, array $options)
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->args = $args;
        $this->format = $format;
        $this->options = $options;
    }
}

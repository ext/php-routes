<?php

namespace Sidvind\PHPRoutes;

class Router
{
    protected $patterns = [];
    private $path_methods = [];

    public function __construct($filename = false)
    {
        if (!$filename) {
            return;
        }

        $get = function ($pattern, array $options = []) {
            $this->addRoute($pattern, 'GET', $options);
        };
        $post = function ($pattern, array $options = []) {
            $this->addRoute($pattern, 'POST', $options);
        };
        $put = function ($pattern, array $options = []) {
            $this->addRoute($pattern, 'PUT', $options);
        };
        $delete = function ($pattern, array $options = []) {
            $this->addRoute($pattern, 'DELETE', $options);
        };
        $resource = function ($pattern, array $options = [], $callback = false) {
            $this->resource($pattern, $options, $callback);
        };
        $scope = function ($pattern, array $options = [], $callback) {
            $this->scope($pattern, $options, $callback);
        };

        include $filename;
    }

    public function formatRoutes($verbose = false)
    {
        $formatter = new RouteFormatter();
        $formatter->verbose = $verbose;
        foreach ($this->patterns as $cur) {
            $formatter->add($cur);
        }
        return (string)$formatter;
    }

    public function printRoutes($verbose = false)
    {
        echo $this->formatRoutes($verbose);
    }

    public function match($url, $method = false)
    {
        $method = $method ?: $_SERVER['REQUEST_METHOD'];

        /* handle HEAD as GET */
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        foreach ($this->patterns as $route) {
            if ($route->method !== $method) {
                continue;
            }

            if (preg_match($route->regex, $url, $match)) {
                foreach ($match as $k => $v) {
                    if (is_numeric($k)) {
                        unset($match[$k]);
                    }
                }

                /* find if format suffix was specified */
                $format = false;
                if (array_key_exists('format', $match)) {
                    $format = substr($match['format'], 1); /* remove dot */
                    $format = $this->mimetype($format);
                    unset($match['format']);
                }

                return new RouterMatch($route->controller, $route->action, $match, $format);
            }
        }
        return null;
    }

    public function mimetype($format)
    {
        /* hack: translate to mimetype. @todo figure out a better way, perhaps /etc/mime.types */
        switch ($format) {
            case 'html':
                return 'text/html';
                break;
            case 'json':
                return 'application/json';
                break;
            case 'md':
                return 'text/markdown';
                break;
            case 'txt':
                return 'text/plain';
                break;
            case 'xml':
                return 'application/xml';
                break;
            case 'svg':
                return 'image/svg+xml';
                break;
            default:
                return $format;
        }
    }

    protected function parseTo($str, $defaultAction = 'index')
    {
        if (!preg_match('/^([A-Z][a-zA-Z0-9]*)?(?:#([a-zA-Z0-9_]+)?)?$/', $str, $match)) {
            throw new \BadFunctionCallException("Malformed 'to'");
        }
        array_shift($match);
        switch (count($match)) {
            case 0:
                return ['Index', $defaultAction];
            case 1:
                return [$match[0], $defaultAction];
            case 2:
                return [$match[0] ?: 'Index', $match[1]];
        }
    }

    protected function defaultAction($pattern)
    {
        if (empty($pattern)) {
            return 'index';
        }
        return preg_replace_callback('#/(:[a-z]+)?#', function ($x) {
            return isset($x[1]) ? '' : '_';
        }, $pattern);
    }

    public function __call($method, $args)
    {
        if (isset($this->path_methods[$method]) && is_callable($this->path_methods[$method])) {
            return call_user_func_array($this->path_methods[$method], $args);
        }

        $trace = debug_backtrace();
        $class = get_class($this);
        trigger_error("Call to undefined method $class::$method from {$trace[0]['file']} " .
                      "on line {$trace[0]['line']}", E_USER_ERROR);
    }

    /**
     * Removes all routes.
     * Mostly useful for tests.
     */
    public function clear()
    {
        $this->patterns = [];
    }

    /**
     * Add a new route.
     *
     * Format:
     * Basic format is '/path/to/match' which literally matches the url.
     * Variables can be assigned using '/foo/:bar' where the second part is a
     * variable called bar. By default variables accept almost anything but the
     * regular expression can be tuned using the option 'bar_format' (see below).
     * Patterns may also contain regular expressions.
     *
     * @param $pattern Route pattern to match, see format description above.
     * @param $method Which HTTP method to match.
     * @param $options Array of options:
     * @option 'to' Target controller/action. [controller][#action]
     * @option 'as' Name of this route.
     * @option 'var_format' Variable format. 'var' should be replaced with the
     *                      actual variable name, like 'bar_format'. The value
     *                      is the RE for matching, e.g. '[0-9]+' for a required
     *                      number.
     */
    public function addRoute($pattern, $method, $options)
    {
        $pattern = trim($pattern, '/');

        /* generate action name */
        $defaultAction = $this->defaultAction($pattern);

        /* default options */
        $options = array_merge([
            'to' => '#' . $defaultAction,
            'as' => false,
        ], $options);
        $as = $options['as'];

        $re = preg_replace_callback('/:([a-z]+)/', function ($x) use ($options) {
            $fmt = '[A-Za-z0-9\-_\.]+'; /* default variable format */
            if (isset($options[$x[1] . '_format'])) {
                $fmt = $options[$x[1] . '_format'];
            }
            return "(?P<{$x[1]}>$fmt)";
        }, $pattern);
        preg_match_all('/:([a-z]+)/', $pattern, $args);

        /* optional format suffix */
        $re .= '(?P<format>\.\w+)?';

        /* push new route */
        list($controller, $action) = $this->parseTo($options['to'], $defaultAction);
        $route = new Route;
        $route->pattern = "/$pattern";
        $route->regex = "#^/$re$#";
        $route->method = $method;
        $route->controller = $controller;
        $route->action = $action;
        $route->name = static::pathFunctionName($as);
        $this->patterns[] = $route;

        return $this->addPathFunction($pattern, $as);
    }

    private static function pathFunctionName($as)
    {
        if ($as === false) {
            return false;
        }
        if (!is_array($as)) {
            return "${as}_path";
        } else {
            return "${as[0]}_path";
        }
    }

    public static function basePath()
    {
        return '/';
    }

    public function generatePath($pattern, $obj = [])
    {
        if (!(is_array($obj) || is_object($obj))) {
            $args = func_get_args();
            $args = array_splice($args, 1);
            return '/' . preg_replace_callback('/:([a-z]+)/', function ($match) use (&$args) {
                return array_shift($args);
            }, $pattern);
        }

        if (is_array($obj)) {
            $obj = (object)$obj;
        }

        return static::basePath() . preg_replace_callback('/:([a-z]+)/', function ($match) use ($obj) {
            $name = $match[1];
            if (!isset($obj->$name)) {
                throw new \BadFunctionCallException("Missing argument {$name}");
            }
            return $obj->$name;
        }, $pattern);
    }

    /**
     * Convert a (resource) pattern to suitable path base.
     */
    private static function pathFunctionBase($pattern)
    {
        return str_replace([':', '/'], ['', '_'], $pattern);
    }

    private function addPathFunction($pattern, $as)
    {
        if (!$as) {
            return;
        }

        if (!is_array($as)) {
            $func = function () use ($pattern) {
                return call_user_func_array([$this, 'generatePath'], array_merge([$pattern], func_get_args()));
            };
        } else {
            $func = $as[1];
        }

        $func_name = static::pathFunctionName($as);
        $callable = \Closure::bind($func, $this, get_class($this));
        $this->path_methods[$func_name] = $callable;

        return $callable;
    }

    protected static function resourceTo($method, $options)
    {
        $prefix = $options['to'];
        switch ($method) {
            case 'list':
                return "{$prefix}#index";
            case 'new':
                return "{$prefix}#make";
            default:
                return "{$prefix}#{$method}";
        }
    }

    /**
     * Create a new context for adding routes directly to root scope.
     */
    public function context()
    {
        return new RootContext($this);
    }

    public function resource($pattern, array $options = [], $callback = false)
    {
        $pattern = trim($pattern, '/');
        $options = array_merge([
            'to' => Utils::classname($pattern),
            'as' => false,
            'id_format' => '\d+',
        ], $options);
        $context = new ResourceContext($pattern, $options, $this);

        $methods = ['list', 'create', 'new', 'update', 'show', 'edit', 'destroy'];
        if (isset($options['only'])) {
            $methods = Utils::filterOnly($methods, $options['only']);
        }
        if (isset($options['except'])) {
            $methods = Utils::filterExcept($methods, $options['except']);
        }

        $as_func = function () use ($pattern) {
            if (func_num_args() == 0) {
                return $this->generatePath($pattern);
            } else {
                return call_user_func_array([$this, 'generatePath'], array_merge(["$pattern/:id"], func_get_args()));
            }
        };

        $as_stem = $options['as'];
        if ($as_stem === false) {
            $as_stem = static::pathFunctionBase($pattern);
        }

        foreach ($methods as $m) {
            $o = array_merge($options, ['to' => static::resourceTo($m, $options)]);
            switch ($m) {
                case 'list':
                    $this->addRoute("/$pattern", 'GET', array_merge($o, ['as' => [$as_stem, $as_func]]));
                    break;
                case 'create':
                    $this->addRoute("/$pattern", 'POST', array_merge($o, ['as' => "create_{$as_stem}"]));
                    break;
                case 'new':
                    $this->addRoute("/$pattern/new", 'GET', array_merge($o, ['as' => "new_{$as_stem}"]));
                    break;
                case 'edit':
                    $this->addRoute("/$pattern/:id/edit", 'GET', array_merge($o, ['as' => "edit_{$as_stem}"]));
                    break;
                case 'show':
                    $this->addRoute("/$pattern/:id", 'GET', array_merge($o, ['as' => [$as_stem, $as_func]]));
                    break;
                case 'destroy':
                    $this->addRoute("/$pattern/:id", 'DELETE', array_merge($o, ['as' => "destroy_{$as_stem}"]));
                    break;
                case 'update':
                    $this->addRoute("/$pattern/:id", 'PUT', array_merge($o, ['as' => "update_{$as_stem}"]));
                    $this->addRoute("/$pattern/:id", 'PATCH', $o);
                    break;
            }
        }
        if ($callback) {
            $callback($context);
        }
    }

    public function scope($pattern, array $options, $callback)
    {
        $pattern = trim($pattern, '/');
        $defaults = [
            'to' => false,
        ];
        $context = new ScopeContext($pattern, array_merge($defaults, $options), $this);

        if ($callback) {
            $callback($context);
        }
    }
}

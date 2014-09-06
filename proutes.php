<?php

function prouter_classname($str){
	return implode('', array_map('ucfirst', explode('/', trim($str,'/'))));
}

function prouter_actionname($str){
	return preg_replace('#/?([^/]+).*#', '\1', $str);
}

function prouter_caller_error($message, $type){
  $stack = debug_backtrace();
  $message .= ", called from {$stack[4]['file']}:{$stack[4]['line']}";
	trigger_error($message, $type);
}

function prouter_generate_path($pattern, $obj=array()){
	if ( !(is_array($obj) || is_object($obj)) ){
		$args = func_get_args();
		$args = array_splice($args, 1);
		return preg_replace_callback('/:([a-z]+)/', function($match) use (&$args) {
			return array_shift($args);
		}, $pattern);
	}

	if ( is_array($obj) ){
		$obj = (object)$obj;
	}

	return preg_replace_callback('/:([a-z]+)/', function($match) use ($obj) {
		$name = $match[1];
		if ( !isset($obj->$name) ){
			//prouter_caller_error("Undefined property \$$name", E_USER_ERROR);
			return '';
		}
		return $obj->$name;
	}, $pattern);
}

class ProuterResourceContext {
	protected $namespace;
	protected $options;
	protected $router;

	public function __construct($namespace, array $options, $router){
		$this->namespace = $namespace;
		$this->options = $options;
		$this->router = $router;
	}

	public function members($callback){
		$context = new ProuterLeafContext("{$this->namespace}/:id", $this->options, $this->router);
		$callback($context);
	}

	public function collection($callback){
		$context = new ProuterLeafContext("{$this->namespace}", $this->options, $this->router);
		$callback($context);
	}
}

class ProuterLeafContext {
	protected $namespace;
	protected $options;
	protected $router;

	public function __construct($namespace, array $options, $router){
		$this->namespace = $namespace;
		$this->options = $options;
		$this->router = $router;
	}

	public function get($pattern, array $options=[]){
		$options = array_merge($this->options, ['to' => $this->options['to'] . '#' . prouter_actionname($pattern)], $options);
		$this->router->method("{$this->namespace}/{$pattern}", 'GET', $options);
	}

	public function post($pattern, array $options=[]){
		$options = array_merge($this->options, ['to' => $this->options['to'] . '#' . prouter_actionname($pattern)], $options);
		$this->router->method("{$this->namespace}/{$pattern}", 'POST', $options);
	}

	public function put($pattern, array $options=[]){
		$options = array_merge($this->options, ['to' => $this->options['to'] . '#' . prouter_actionname($pattern)], $options);
		$this->router->method("{$this->namespace}/{$pattern}", 'PUT', $options);
	}

	public function delete($pattern, array $options=[]){
		$options = array_merge($this->options, ['to' => $this->options['to'] . '#' . prouter_actionname($pattern)], $options);
		$this->router->method("{$this->namespace}/{$pattern}", 'DELETE', $options);
	}

	public function method($pattern, $method, array $options=[]){
		$options = array_merge($this->options, ['to' => $this->options['to'] . '#' . prouter_actionname($pattern)], $options);
		$this->router->method("{$this->namespace}/{$pattern}", $method, $options);
	}
};

class ProuterNamespaceContext extends ProuterLeafContext {
	public function resource($pattern, array $options=[], $callback=false){
		$this->router->resource("{$this->namespace}/{$pattern}", array_merge($this->options, ['to' => $this->options['to'] . prouter_classname($pattern)], $options), $callback);
	}

	public function scope($pattern, array $options=[], $callback=false){
		$this->router->scope("{$this->namespace}/$pattern", array_merge($this->options, ['to' => $this->options['to'] . prouter_classname($pattern)], $options), $callback);
	}
};

class ProuterPattern {
	public $pattern;
	public $re;
	public $as;
	public $method;
	public $controller;
	public $action;
};

class Prouter {
	protected $patterns = array();
	private $path_methods = array();

	public function __construct($filename=false){
		if ( !$filename ) return;
		$get       = function($pattern, array $options=[]){ $this->method($pattern, 'GET',    $options); };
		$post      = function($pattern, array $options=[]){ $this->method($pattern, 'POST',   $options); };
		$put       = function($pattern, array $options=[]){ $this->method($pattern, 'PUT',    $options); };
		$delete    = function($pattern, array $options=[]){ $this->method($pattern, 'DELETE', $options); };
		$resource  = function($pattern, array $options=[], $callback=false){ $this->resource($pattern, $options, $callback); };
		$scope     = function($pattern, array $options=[], $callback){ $this->scope($pattern, $options, $callback); };
		include $filename;
	}

	public function print_routes(){
		foreach ( $this->patterns as $cur ){
			list($pattern, $_, $method, $controller, $action, $as) = $cur;
			$func = "{$controller}#{$action}";
			printf("%30.30s %-6s %-30s %-16s   %s\n",
						 preg_replace('/_path$/', '', $as),
						 $method, $pattern, $func, $_);
		}
	}

	public function match($url, $method=false){
		$method = $method ?: $_SERVER['REQUEST_METHOD'];
		foreach ( $this->patterns as $cur ){
			list($pattern, $re, $cur_method, $controller, $action) = $cur;
			if ( $cur_method !== $method ) continue;

			if ( preg_match($re, $url, $match) ){
				foreach ( $match as $k => $v ){
					if ( is_numeric($k) ) unset($match[$k]);
				}
				return array($controller, $action, $match);
			}
		}
		return array(false, false, false);
	}

	protected function parse_to($str){
		if ( !preg_match('/([A-Z][a-zA-Z0-9]*)?(?:#([a-zA-Z0-9_]+))?/', $str, $match) ){
			return ['Index', 'index'];
		}
		array_shift($match);
		switch ( count($match) ){
			case 0: return ['Index', 'index'];
			case 1: return [$match[0], 'index'];
			case 2: return [$match[0] ?: 'Index', $match[1]];
		}
	}

	protected function default_action($pattern){
		return '#' . preg_replace_callback('#/(:[a-z]+)?#', function($x){ return isset($x[1]) ? '' : '_'; }, $pattern);
	}

	public function __call($method, $args){
		if ( isset($this->path_methods[$method]) && is_callable($this->path_methods[$method]) ){
      return call_user_func_array($this->path_methods[$method], $args);
    }

		$trace = debug_backtrace();
		$class = get_class($this);
    trigger_error("Call to undefined method $class::$method from {$trace[0]['file']} on line {$trace[0]['line']}", E_USER_ERROR);
    return null;
	}

	/**
	 * Removes all routes.
	 * Mostly useful for tests.
	 */
	public function clear(){
		$this->patterns = array();
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
	public function method($pattern, $method, $options){
		$pattern = trim($pattern, '/');

		/* generate action name */
		$action = $this->default_action($pattern);
		$options = array_merge(['to' => $action], $options);
		if ( strstr($options['to'], '#') === false ){
			$options['to'] .= $action;
		}

		/* default options */
		$options = array_merge([
			'as' => false,
		], $options);
		$as = $options['as'];

		$re = preg_replace_callback('/:([a-z]+)/', function($x) use ($options) {
			$fmt = '[A-Za-z0-9\-_\.]+'; /* default variable format */
			if ( isset($options[$x[1] . '_format']) ){
				$fmt = $options[$x[1] . '_format'];
			}
			return "(?P<{$x[1]}>$fmt)";
		}, $pattern);
		preg_match_all('/:([a-z]+)/', $pattern, $args);

		list($controller, $action) = $this->parse_to($options['to']);
		$this->patterns[] = array("/$pattern", "#^/$re$#", $method, $controller, $action, static::path_function_name($as));

		$this->add_path_function($pattern, $as);
	}

	private static function path_function_name($as){
		if ( $as === false ) return false;
		if ( !is_array($as) ){
			return "${as}_path";
		} else {
			return "${as[0]}_path";
		}
	}

	/**
	 * Convert a (resource) pattern to suitable path base.
	 */
	private static function path_function_base($pattern){
		return str_replace([':', '/'], ['', '_'], $pattern);
	}

	private function add_path_function($pattern, $as){
		if ( !$as ) return;

		if ( !is_array($as) ){
			$func = function() use ($pattern) {
				return call_user_func_array('prouter_generate_path', array_merge([$pattern], func_get_args()));
			};
		} else {
			$func = $as[1];
		}

		$func_name = static::path_function_name($as);
		$this->path_methods[$func_name] = \Closure::bind($func, $this, get_class($this));
	}

	public function resource($pattern, array $options=array(), $callback=false){
		$pattern = trim($pattern, '/');
		$options = array_merge(['to' => prouter_classname($pattern)], $options);
		$context = new ProuterResourceContext($pattern, $options, $this);

		$methods = ['index', 'create', 'new', 'update', 'show', 'edit', 'destroy'];
		if ( isset($options['only']) ){
			$methods = $options['only'];
		}
		if ( isset($options['except']) ){
			$methods = array_filter($methods, function($x) use ($options) { return !in_array($x, $options['except']); });
		}

		$as_func = function(){
			if ( func_num_args() == 0 ){
				return prouter_generate_path("/$pattern");
			} else {
				return call_user_func_array('prouter_generate_path', array_merge(["/$pattern/:id"], func_get_args()));
			}
		};

		$as_stem = static::path_function_base($pattern);

		foreach ( $methods as $m ){
			$o = array_merge($options, ['to' => $options['to'] . '#' . $m]);
			switch ( $m ){
				case 'index':   $this->method("/$pattern",          'GET',    array_merge($o, ['as' => [$as_stem, $as_func]])); break;
				case 'create':  $this->method("/$pattern",          'POST',   array_merge($o, ['as' => "create_{$as_stem}"])); break;
				case 'new':     $this->method("/$pattern/new",      'GET',    array_merge($o, ['as' => "new_{$as_stem}"])); break;
				case 'edit':    $this->method("/$pattern/:id/edit", 'GET',    array_merge($o, ['as' => "edit_{$as_stem}"])); break;
				case 'show':    $this->method("/$pattern/:id",      'GET',    array_merge($o, ['as' => [$as_stem, $as_func]])); break;
				case 'destroy': $this->method("/$pattern/:id",      'DELETE', array_merge($o, ['as' => "destroy_{$as_stem}"])); break;
				case 'update':
					$this->method("/$pattern/:id", 'PUT',    array_merge($o, ['as' => "update_{$as_stem}"]));
					$this->method("/$pattern/:id", 'PATCH',  $o);
					break;
			}
		}
		if ( $callback ){
			$callback($context);
		}
	}

	public function scope($pattern, array $options, $callback){
		$pattern = trim($pattern, '/');
		$context = new ProuterNamespaceContext($pattern, array_merge(['to' => prouter_classname($pattern)], $options), $this);

		if ( $callback ){
			$callback($context);
		}
	}
}

if ( php_sapi_name() === 'cli' && basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__) ){
	if ( $argc == 1 ){
		echo "usage: {$argv[0]} FILENAME [pattern..]\n";
		exit;
	}

	$router = new Prouter($argv[1]);

	if ( $argc == 2 ){
		$router->print_routes();
	} else {
		var_dump($router->derp_path(7));

		foreach ( array_slice($argv, 2) as $pattern ){
			list($controller, $action, $args) = $router->match($pattern, 'GET');
			if ( $controller ){
				echo "$controller::$action(" . var_export($args, true) . ")\n";
			} else {
				echo "{$argv[0]}: pattern doesn't match any route.\n";
				exit(1);
			}
		}
	}
}

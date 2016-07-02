<?php

namespace Sidvind\PHPRoutes;

class Router {
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
		$func_width = array_reduce($this->patterns, function($max, $x){ return max($max, strlen($x[3]) + strlen($x[4])); }, 0) + 3;
		foreach ( $this->patterns as $cur ) {
			list($pattern, $_, $method, $controller, $action, $as) = $cur;
			$func = "{$controller}#{$action}";
			printf("%30.30s %-6s %-30s %-{$func_width}s %s\n",
						 preg_replace('/_path$/', '', $as),
						 $method, $pattern, $func, $_);
		}
	}

	public function match($url, $method=false){
		$method = $method ?: $_SERVER['REQUEST_METHOD'];

		/* handle HEAD as GET */
		if ( $method === 'HEAD' ){
			$method = 'GET';
		}

		foreach ( $this->patterns as $cur ){
			list(, $re, $cur_method, $controller, $action) = $cur;
			if ( $cur_method !== $method ) continue;

			if ( preg_match($re, $url, $match) ){
				foreach ( $match as $k => $v ){
					if ( is_numeric($k) ) unset($match[$k]);
				}

				/* find if format suffix was specified */
				$format = false;
				if ( array_key_exists('format', $match) ){
					$format = substr($match['format'], 1) /* remove dot */;
					unset($match['format']);

					/* hack: translate to mimetype. @todo figure out a better way, perhaps /etc/mime.types */
					switch ( $format ){
						case 'html': $format = 'text/html'; break;
						case 'json': $format = 'application/json'; break;
						case 'md': $format = 'text/markdown'; break;
						case 'txt': $format = 'text/plain'; break;
						case 'xml': $format = 'application/xml'; break;
						case 'svg': $format = 'image/svg+xml'; break;
					}
				}

				return new RouterMatch($controller, $action, $match, $format);
			}
		}
		return null;
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

		/* optional format suffix */
		$re .= '(?P<format>\.\w+)?';

		list($controller, $action) = $this->parse_to($options['to']);
		$this->patterns[] = array("/$pattern", "#^/$re$#", $method, $controller, $action, static::path_function_name($as));

		return $this->add_path_function($pattern, $as);
	}

	private static function path_function_name($as){
		if ( $as === false ) return false;
		if ( !is_array($as) ){
			return "${as}_path";
		} else {
			return "${as[0]}_path";
		}
	}

	public static function base_path(){
		return '/';
	}

	public function generate_path($pattern, $obj=array()){
		if ( !(is_array($obj) || is_object($obj)) ){
			$args = func_get_args();
			$args = array_splice($args, 1);
			return '/' . preg_replace_callback('/:([a-z]+)/', function($match) use (&$args) {
			return array_shift($args);
			}, $pattern);
		}

		if ( is_array($obj) ){
			$obj = (object)$obj;
		}

		return static::base_path() . preg_replace_callback('/:([a-z]+)/', function($match) use ($obj) {
			$name = $match[1];
			if ( !isset($obj->$name) ){
				throw new \BadFunctionCallException("Missing argument {$name}");
			}
			return $obj->$name;
		}, $pattern);
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
				return call_user_func_array(array($this, 'generate_path'), array_merge([$pattern], func_get_args()));
			};
		} else {
			$func = $as[1];
		}

		$func_name = static::path_function_name($as);
		$callable = \Closure::bind($func, $this, get_class($this));
		$this->path_methods[$func_name] = $callable;

		return $callable;
	}

	public function resource($pattern, array $options=array(), $callback=false){
		$pattern = trim($pattern, '/');
		$options = array_merge([
			'to' => Utils::classname($pattern),
			'as' => false,
			'id_format' => '\d+',
		], $options);
		$context = new ResourceContext($pattern, $options, $this);

		$methods = ['list', 'create', 'new', 'update', 'show', 'edit', 'destroy'];
		if ( isset($options['only']) ){
			$methods = $options['only'];
		}
		if ( isset($options['except']) ){
			$methods = array_filter($methods, function($x) use ($options) { return !in_array($x, $options['except']); });
		}

		$as_func = function() use ($pattern) {
			if ( func_num_args() == 0 ){
				return $this->generate_path($pattern);
			} else {
				return call_user_func_array(array($this, 'generate_path'), array_merge(["$pattern/:id"], func_get_args()));
			}
		};

		$as_stem = $options['as'];
		if ( $as_stem === false ){
			$as_stem = static::path_function_base($pattern);
		}

		foreach ( $methods as $m ){
			$o = array_merge($options, ['to' => $options['to'] . '#' . $m]);
			switch ( $m ){
				case 'list':    $this->method("/$pattern",          'GET',    array_merge($o, ['as' => [$as_stem, $as_func]])); break;
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
		$context = new ScopeContext($pattern, array_merge(['to' => Utils::classname($pattern)], $options), $this);

		if ( $callback ){
			$callback($context);
		}
	}
}

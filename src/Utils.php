<?php

namespace Sidvind\PHPRoutes;

class Utils {
	public static function classname($str){
		return implode('', array_map('ucfirst', explode('/', trim($str,'/'))));
	}

	public static function actionname($str){
		return preg_replace('#/?([^/]+).*#', '\1', $str);
	}

	/**
	 * Filter an array with whitelisted values
	 *
	 * @param $only string|array whitelisted values
	 */
	public static function filterOnly(array $methods, $only){
		$only = is_array($only) ? $only : [$only];
		return array_filter($methods, function($x) use ($only) {
			return in_array($x, $only);
		});
	}

	/**
	 * Filter an array with blacklisted values
	 *
	 * @param $except string|array blacklisted values
	 */
	public static function filter_except(array $methods, $except){
		$except = is_array($except) ? $except : [$except];
		return array_filter($methods, function($x) use ($except) {
			return !in_array($x, $except);
		});
	}
}

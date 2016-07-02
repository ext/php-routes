<?php

namespace Sidvind\PHPRoutes;

class Utils {
	public static function classname($str){
		return implode('', array_map('ucfirst', explode('/', trim($str,'/'))));
	}

	public static function actionname($str){
		return preg_replace('#/?([^/]+).*#', '\1', $str);
	}
}

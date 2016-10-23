<?php

namespace Sidvind\PHPRoutes;

class RouteFormatter {
	protected $lines = [];

	public function add($route){
		list($pattern, $re, $method, $controller, $action, $as) = $route;
		$this->lines[] = [
			preg_replace('/_path$/', '', $as),
			$method,
			$pattern,
			"{$controller}#{$action}",
			$re
		];
	}

	protected function columnWidths(){
		$columns = count($this->lines[0]);
		$width = [];
		for ( $i = 0; $i < $columns; $i++ ){
			$width[] = array_reduce($this->lines, function($max, $line) use ($i) {
				return max($max, strlen($line[$i]));
			}, 0);
		}
		return $width;
	}

	public function __toString(){
		if ( count($this->lines) === 0 ){
			return '';
		}

		/* header */
		array_unshift($this->lines, [
			'NAME',
			'METHOD',
			'PATTERN',
			'TO',
			'REGEXP',
		]);

		$columns = count($this->lines[0]);
		$width = $this->columnWidths();
		$output = '';

		foreach ( $this->lines as $line ){
			for ( $i = 0; $i < $columns; $i++ ){
				$w = $width[$i] + 4;
				$output .= sprintf("%-{$w}s", $line[$i]);
			}
			$output .= "\n";
		}

		return $output;
	}
}

<?php

namespace Sidvind\PHPRoutes;

class RouteFormatter
{
    protected $lines = [];
    public $verbose = false;

    public function add($route)
    {
        $line = [
            preg_replace('/_path$/', '', $route->name),
            $route->method,
            $route->pattern,
            "{$route->controller}#{$route->action}",
        ];
        if ($this->verbose) {
            $line[] = $route->regex;
        }
        $this->lines[] = $line;
    }

    protected function columnWidths()
    {
        $columns = count($this->lines[0]);
        $width = [];
        for ($i = 0; $i < $columns; $i++) {
            $width[] = array_reduce($this->lines, function ($max, $line) use ($i) {
                return max($max, strlen($line[$i]));
            }, 0);
        }
        return $width;
    }

    public function __toString()
    {
        if (count($this->lines) === 0) {
            return '';
        }

        /* header */
        $headings = [
            'NAME',
            'METHOD',
            'PATTERN',
            'TO',
        ];
        if ($this->verbose) {
            $headings[] = 'REGEXP';
        }
        array_unshift($this->lines, $headings);

        $columns = count($this->lines[0]);
        $width = $this->columnWidths();
        $output = '';

        foreach ($this->lines as $line) {
            for ($i = 0; $i < $columns; $i++) {
                $w = $width[$i] + 4;
                $output .= sprintf("%-{$w}s", $line[$i]);
            }
            $output .= "\n";
        }

        return $output;
    }
}

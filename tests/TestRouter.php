<?php

namespace Testing;

class TestRouter extends \Sidvind\PHPRoutes\Router
{
    /* expose */
    public function parseTo($str, $defaultAction = null)
    {
        /* keep default from parent call */
        if ($defaultAction === null) {
            return parent::parseTo($str);
        } else {
            return parent::parseTo($str, $defaultAction);
        }
    }

    public function numRoutes()
    {
        return count($this->patterns);
    }

    public function testName($pattern)
    {
        $this->addRoute($pattern, 'GET', []);
        return $this->patterns[0]->action;
    }
}

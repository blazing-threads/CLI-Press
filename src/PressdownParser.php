<?php

/*
 * This file is part of CLI Press.
 *
 * The MIT License (MIT)
 * Copyright © 2017
 *
 * Alex Carter, alex@blazeworx.com
 * Keith E. Freeman, cli-press@forsaken-threads.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that should have been distributed with this source code.
 */

namespace BlazingThreads\CliPress;

class PressdownParser extends \ParsedownExtra
{

    /**
     * Array of patterns and callbacks
     * @var array
     */
    protected $directives;

    /**
     * If the last parsed markup contained the Font Awesome directive.
     * @var bool
     */
    protected $hasFA = false;

    public function __construct()
    {
        parent::__construct();
        $this->directives['fontAwesome'] = '/(@|)\{f@([a-z0-9 -]+)\}/';
        $this->directives['pageBreak'] = '/(@|)\{break}/';
    }

    protected function fontAwesome($matches)
    {
        if ($matches[1]) {
            return substr($matches[0], 1);
        }

        $this->hasFA = true;

        $matches = explode(' ', $matches[2]);
        $icon = array_shift($matches);

        $classes = '';
        foreach ($matches as $class) {
            if (preg_match('/^(lg|[2-5]x)|(rotate-(9|18|27)0)|flip-(horizontal|vertical)/', $class)) {
                $classes .= ' fa-' . $class;
            }
        }

        return "<i class=\"fa fa-$icon$classes\"></i>";
    }

    protected function pageBreak($matches)
    {
        if ($matches[1]) {
            return substr($matches[0], 1);
        }

        return '<div class="break"></div>';
    }

    /**
     * @param $markup
     * @return PressedLeaf
     */
    public function parse($markup)
    {
        $markup = parent::parse($markup);
        $markup = $this->processDirectives($markup);
        $leaf = new PressedLeaf($markup, $this->hasFA);
        $this->hasFA = false;
        return $leaf;
    }

    protected function processDirectives($markup)
    {
        foreach ($this->directives as $callback => $pattern) {
            $markup = preg_replace_callback($pattern, [$this, $callback], $markup);
        }
        return $markup;
    }
}
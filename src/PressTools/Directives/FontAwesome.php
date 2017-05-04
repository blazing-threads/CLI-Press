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

namespace BlazingThreads\CliPress\PressTools\Directives;

class FontAwesome extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@|)\{f@([a-z0-9 -]+)\}/';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        return $markup->addLiteral('{')
            ->addDirective('f@')
            ->addOption($matches[2])
            ->addLiteral('}');
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        $matches = explode(' ', $matches[2]);
        $icon = array_shift($matches);

        $classes = '';
        foreach ($matches as $class) {
            if (preg_match('/^(lg|[2-5]x)|(rotate-(9|18|27)0)|flip-(horizontal|vertical)/', $class)) {
                $classes .= ' fa-' . $class;
            } else {
                $classes .= " $class";
            }
        }

        return "<i class=\"fa fa-$icon$classes\"></i>";
    }
}
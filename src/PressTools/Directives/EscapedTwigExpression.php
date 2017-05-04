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

class EscapedTwigExpression extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@|)\{@\{ (.+) \}\}/';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        return $markup->addLiteral('{@{')
            ->addOption(' ' . $matches[2] . ' ')
            ->addLiteral('}}');
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        return "{{ $matches[2] }}";
    }
}
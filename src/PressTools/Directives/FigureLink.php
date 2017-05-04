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

class FigureLink extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@|)f\{(.*)\}\((.+)\)/U';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        return $markup->addDirective('f')
            ->addLiteral('{')
            ->addPressdown($matches[2])
            ->addLiteral('}(')
            ->addOption($matches[3])
            ->addLiteral(')');
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        if (! $figure = app()->make(Figure::class)->getFigure($matches[3])) {
            return '';
        }

        $text = empty($matches[2]) ? '' : ": $matches[2]";

        return "<a href=\"curator#figure-$matches[3]\">Figure $figure$text</a>";
    }
}
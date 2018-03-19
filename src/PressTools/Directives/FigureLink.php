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
        $markup = new SyntaxHighlighter(true);
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
            var_dump($matches);
            return '';
        }

        if (!empty($matches[2])) {
            $caption = $matches[2];
        } else {
            $caption = empty($figure[0]) ? 'Figure' : "Figure $figure[0]: $figure[1]";
        }

        return "<a href=\"curator#figure-$matches[3]\">$caption</a>";
    }
}
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

class PageBreak extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@|)\{break}/';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new ColorCoder();
        return $markup->addLiteral('{')
            ->addDirective('break')
            ->addLiteral('}');
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        return '<div class="break"></div>';
    }
}
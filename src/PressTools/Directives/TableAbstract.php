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

class TableAbstract extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/@table-cols\{content\}\(caption\?\)#anchor-name/';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        return $this->abstractDirective();
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        return $this->abstractDirective();
    }

    /**
     * @return SyntaxHighlighter
     */
    protected function abstractDirective()
    {
        $markup = new SyntaxHighlighter();
        return $markup->addDirective('table')
            ->addLiteral('-')
            ->addOption('cols')
            ->addLiteral('{')
            ->addPressdown('content')
            ->addLiteral('}(')
            ->addPressdown('caption?')
            ->addLiteral(')#')
            ->addOption('anchor-name');
    }
}
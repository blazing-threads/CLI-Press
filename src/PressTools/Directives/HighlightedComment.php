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

class HighlightedComment extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@)\/\/.+$/m';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        return $markup->addPlainText(substr($matches[0], 1));
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        // this should never get called
        return 'bad juju';
    }
}
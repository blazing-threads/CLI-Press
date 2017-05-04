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

use BlazingThreads\CliPress\PressTools\PressdownParser;

class PullQuoteAnchor extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@|)pqa\{([a-zA-Z0-9_-]+)\}/U';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        return $markup->addDirective('pqa')
            ->addLiteral('{')
            ->addOption($matches[2])
            ->addLiteral('}');
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        if (! $pullQuote = app()->make(PullQuote::class)->getPullQuote($matches[2])) {
            return '';
        }

        return app()->make(PressdownParser::class)->processDirectives('block', $pullQuote);
    }
}
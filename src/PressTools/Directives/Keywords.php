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

class Keywords extends BaseDirective
{

    /**
     * @var string
     */
    protected $pattern = '/(@*|)(\^+)([^\^\n\r\f]+)\2/';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        if ($matches[1] == '@@') {
            return substr($matches[0], 2);
        }
        $markup = new SyntaxHighlighter();
        return $markup->addLiteral($matches[2])
            ->addPlainText($matches[3])
            ->addLiteral($matches[2]);
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        $modifier = strlen($matches[2]);
        return "<em class=\"keyword$modifier\">$matches[3]</em>";
    }
}
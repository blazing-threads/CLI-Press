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

use BlazingThreads\CliPress\CliPressException;

class Alert extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@|)alert\{(.+)\}\(([a-z ?-]+)\)/U';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        return $markup->addDirective('alert')
            ->addLiteral('{')
            ->addPressdown($matches[2])
            ->addLiteral('}')
            ->addLiteral('(')
            ->addOption($matches[3])
            ->addLiteral(')');
    }

    /**
     * @param $matches
     * @return string
     * @throws CliPressException
     */
    protected function process($matches)
    {
        $content = $this->parseMarkdown($matches[2]);
        return "<div class=\"pd-alert-box\"><i class=\"fa fa-$matches[3] fa-4x pd-alert-icon\"></i><div class=\"pd-alert\">$content</div><div class=\"clearfix\"></div></div>";
    }
}
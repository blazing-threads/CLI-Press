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
use BlazingThreads\CliPress\PressTools\PressInstructionStack;

class Custom extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@|)custom\{(.+)\}\(([a-z -]+)\)/sUm';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        return $markup->addDirective('custom')
            ->addLiteral('{')
            ->addPressdown($matches[2])
            ->addLiteral('}')
            ->addLiteral('(')
            ->addOption($matches[3])
            ->addLiteral(')');
    }

    protected function findCustomDirective($name)
    {
        $customDirectives = app()->make(PressInstructionStack::class)->customDirectives;

        if (empty($customDirectives[$name])) {
            throw new CliPressException("Cannot find custom directive: " . @(string) $name);
        }

        if (empty($customDirectives[$name]['tag'])) {
            throw new CliPressException("Custom directive '$name' is missing a 'tag' property.");
        }

        $tag = $customDirectives[$name]['tag'];
        $classes = empty($customDirectives[$name]['class']) ? '' : " class=\"{$customDirectives[$name]['class']}\"";
        return [$tag, $classes];
    }

    /**
     * @param $matches
     * @return string
     * @throws CliPressException
     */
    protected function process($matches)
    {
        list($tag, $classes) = $this->findCustomDirective($matches[3]);
        $content = $this->parseMarkdown($matches[2]);
        return "<$tag$classes>$content</$tag>";
    }
}
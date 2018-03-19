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
use BlazingThreads\CliPress\PressTools\PressdownParser;

class Custom extends BaseDirective
{
    /**
     * @var array
     */
    protected $directives = [];

    /**
     * @var string
     */
    protected $pattern = '/(@|)custom(-\d|-level\?)?\{(.+)\}\(([a-z-]+?) *([a-zA-Z0-9\? +-]+)?\)(?(2)\2|)/sUm';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        $markup->addDirective('custom');

        if ($matches[2]) {
            $markup->addLiteral($matches[2]);
        }

        $markup->addLiteral('{')
            ->addPressdown($matches[3])
            ->addLiteral('}')
            ->addLiteral('(')
            ->addOption($matches[4]);

        if (!empty($matches[5])) {
            $markup->addOption(' ' . $matches[5]);
        }

        $markup->addLiteral(')');

        if (!empty($matches[2])) {
            $markup->addLiteral($matches[2]);
        }

        return $markup;
    }

    /**
     * @param $name
     * @return array
     * @throws CliPressException
     */
    protected function findCustomDirective($name)
    {
        if (isset($this->directives[$name])) {
            return $this->directives[$name];
        }

        $customDirectives = app()->instructions()->customDirectives;

        if (empty($customDirectives[$name])) {
            throw new CliPressException("Cannot find custom directive: " . @(string) $name);
        }

        if (empty($customDirectives[$name]['tag'])) {
            throw new CliPressException("Custom directive '$name' is missing a 'tag' property.");
        }

        $tag = $customDirectives[$name]['tag'];
        $classes = empty($customDirectives[$name]['class']) ? '' : " class=\"{$customDirectives[$name]['class']}\"";
        $options = empty($customDirectives[$name]['options']) ? [] : explode(' ', @(string) $customDirectives[$name]['options']);

        return $this->directives[$name] = [$tag, $classes, $options];
    }

    /**
     * @param $matches
     * @return string
     * @throws CliPressException
     */
    protected function process($matches)
    {
        list($tag, $classes, $directiveOptions) = $this->findCustomDirective($matches[4]);

        $instanceOptions = empty($matches[5]) ? [] : explode(' ', trim($matches[5]));

        $stripPTags = !empty($directiveOptions) && in_array('-p', $directiveOptions);
        if ($stripPTags && in_array('+p', $instanceOptions)) {
            $stripPTags = false;
        } elseif (!$stripPTags && in_array('-p', $instanceOptions)) {
            $stripPTags = true;
        }

        $p2br = !empty($directiveOptions) && in_array('-p2br', $directiveOptions);
        if ($p2br && in_array('+p2br', $instanceOptions)) {
            $p2br = false;
        } elseif (!$p2br && in_array('-p2br', $instanceOptions)) {
            $p2br = true;
        }

        if (!in_array('-final', $directiveOptions) && !in_array('-final', $instanceOptions)) {
            $matches[3] = preg_replace_callback($this->pattern, [$this, 'processDirective'], $matches[3]);
            $matches[3] = $this->parseMarkdown($matches[3], $stripPTags);
        }

        if ($stripPTags) {
            $matches[3] = PressdownParser::stripMarkdownPTags($matches[3]);
        } elseif ($p2br) {
            $matches[3] = PressdownParser::changeMarkdownPTagsToBrTags($matches[3]);
        }

        return "<$tag$classes>$matches[3]</$tag>";
    }
}
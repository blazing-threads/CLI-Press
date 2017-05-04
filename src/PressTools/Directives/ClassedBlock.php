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

class ClassedBlock extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@|)\{@([abcdflopsux]|fc|blockquote|code|div|figure|figcaption|li|ol|pre|span|tag|ul)-([a-zA-Z-\.]*)\s+(.+)\s+\2@\}(\(([a-z -]*)\))??/sUm';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        $markup->addLiteral('{@')
            ->addDirective($matches[2])
            ->addLiteral('-')
            ->addOption($matches[3])
            ->addPressdown(' ' . $matches[4] . ' ')
            ->addDirective($matches[2])
            ->addLiteral('@}');

        if (!empty($matches[6])) {
            $markup->addLiteral('(')
                ->addOption($matches[6])
                ->addLiteral(')');
        }

        return $markup;
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        $stripPTags = !empty($matches[6]) && $matches[6] == '-p';
        $matches[4] = $this->parseMarkdown($matches[4], $stripPTags);
        $matches[4] = app()->make(PressdownParser::class)->processDirectives('block', $matches[4]); //preg_replace_callback($this->pattern, [$this, 'processDirective'], $matches[4]);
        switch ($matches[2]) {
            case 'a':
                $tag = 'a';
                break;
            case 'b':
                // pass-through
            case 'blockquote':
                $tag = 'blockquote';
                break;
            case 'c':
                // pass-through
            case 'code':
                $tag = 'code';
                break;
            case 'f':
                // pass-through
            case 'figure':
                $tag = 'figure';
                break;
            case 'fc':
                // pass-through
            case 'figcaption':
                $tag = 'figcaption';
                break;
            case 'd':
                // pass-through
            case 'div':
                $tag = 'div';
                break;
            case 'l':
                // pass-through
            case 'li':
                $tag = 'li';
                break;
            case 'o':
                // pass-through
            case 'ol':
                $tag = 'ol';
                break;
            case 'p':
                $tag = 'p';
                break;
            case 'pre':
                $tag = 'pre';
                break;
            case 's':
                // pass-through
            case 'span':
                $tag = 'span';
                break;
            case 'u':
                // pass-through
            case 'ul':
                $tag = 'ul';
                break;
            default:
                $tag = 'div';
        }
        $class = empty($matches[3]) ? '' : ' class="' . str_replace('.', ' ', $matches[3]) . '"';
        return "<$tag$class>$matches[4]</$tag>";
    }
}
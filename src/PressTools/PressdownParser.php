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

namespace BlazingThreads\CliPress\PressTools;

use BlazingThreads\CliPress\CliPressException;
use BlazingThreads\CliPress\PressTools\Directives\BaseDirective;

class PressdownParser extends \ParsedownExtra
{
    /**
     * @var array
     */
    protected $blockDirectives = [];

    /**
     * @var array
     */
    protected $figures = [];

    /**
     * @var array
     */
    protected $finalDirectives = [];

    /**
     * Array of patterns and callbacks for post processing directives
     * @var array
     */
    protected $postDirectives = [];

    /**
     * Array of patterns and callbacks for pre processing directives
     * @var array
     */
    protected $preDirectives = [];

    /**
     * @param $markup
     * @return mixed
     */
    public function close($markup)
    {
        $markup = $this->processDirectives('post', $markup);
        return $this->processDirectives('final', $markup);
    }

    /**
     * @param $markup
     * @return string
     */
    public function parse($markup)
    {
        $markup = $this->processDirectives('pre', $markup);
        $markup = $this->processDirectives('block', $markup);
        return parent::parse($markup);
    }

    /**
     * @param $markup
     * @param bool $stripPTags
     * @return mixed|string
     */
    public function parseMarkdown($markup, $stripPTags = true)
    {
        $markup = parent::parse($markup);
        return $stripPTags ? $this->stripMarkdownPTags($markup) : $markup;
    }

    /**
     * @param $type
     * @param $markup
     * @return mixed
     */
    public function processDirectives($type, $markup)
    {
        $directives = $type . 'Directives';
        foreach ($this->$directives as $directive) {
            /** @var BaseDirective $directive */
            $markup = preg_replace_callback($directive->getPattern(), [$directive, 'processDirective'], $markup);
        }

        return $markup;
    }

    /**
     * @param $type
     * @param BaseDirective $directive
     * @throws CliPressException
     */
    public function registerDirective($type, BaseDirective $directive)
    {
        if (!preg_match('/(pre|post|block|final)/', $type)) {
            throw new CliPressException("Expected one of: pre, post, block, final.  Received: " . @(string) $type);
        }

        $type = $type . 'Directives';
        array_push($this->$type, $directive);
    }

    /**
     * @param $markup
     * @return mixed
     */
    protected function stripMarkdownPTags($markup)
    {
        return preg_replace('/<\/?p>/', '', $markup);
    }
}
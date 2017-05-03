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

abstract class BaseDirective
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @param $matches
     * @return string
     */
    abstract protected function escape($matches);

    /**
     * @param $matches
     * @return string
     */
    abstract protected function process($matches);

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param $matches
     * @return string
     */
    public function processDirective($matches)
    {
        if (!empty($matches[1])) {
            $whitespace = str_replace('@', '', $matches[1]);
            return $whitespace . $this->escape($matches); //substr($matches[0], strlen($matches[1]));
        }

        return $this->process($matches);
    }

    /**
     * @param $markup
     * @param bool $stripPTags
     * @return mixed
     */
    protected function parseMarkdown($markup, $stripPTags = true)
    {
        return app()->make(PressdownParser::class)->parseMarkdown($markup, $stripPTags);
    }
}
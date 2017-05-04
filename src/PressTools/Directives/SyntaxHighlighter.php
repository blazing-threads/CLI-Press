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

class SyntaxHighlighter
{
    /**
     * @var string
     */
    protected $markup = '';

    /**
     * ColorCoder constructor.
     */
    public function __construct()
    {
        $this->addMarkup('<code class="pressdown">');
    }

    /**
     * @param $directive
     * @return SyntaxHighlighter
     */
    public function addDirective($directive)
    {
        return $this->addMarkup("<span class=\"pd-directive\">$directive</span>");
    }

    /**
     * @param $literal
     * @return SyntaxHighlighter
     */
    public function addLiteral($literal)
    {
        return $this->addMarkup("<span class=\"pd-literal\">$literal</span>");
    }

    /**
     * @param $option
     * @return SyntaxHighlighter
     */
    public function addOption($option)
    {
        return $this->addMarkup("<span class=\"pd-option\">$option</span>");
    }

    /**
     * @param $text
     * @return SyntaxHighlighter
     */
    public function addPlainText($text)
    {
        return $this->addMarkup($text);
    }

    /**
     * @param $pressdown
     * @return SyntaxHighlighter
     */
    public function addPressdown($pressdown)
    {
        return $this->addMarkup("<span class=\"pd-pressdown\">$pressdown</span>");
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->markup .= '</code>';
    }

    /**
     * @param $markup
     * @return $this
     */
    protected function addMarkup($markup)
    {
        $this->markup .= $markup;
        return $this;
    }
}
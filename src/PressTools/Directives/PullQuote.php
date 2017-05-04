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

class PullQuote extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@|)pq\{(.+)\}\((left|right|float\?|)\s?([a-zA-Z0-9_-]*\??)\)/U';

    /**
     * @var array
     */
    protected $pullQuotes = [];

    /**
     * @param $pullQuote
     * @return bool|mixed
     */
    public function getPullQuote($pullQuote)
    {
        return $this->hasPullQuote($pullQuote) ? $this->pullQuotes[$pullQuote] : false;
    }

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        $markup->addDirective('pq')
            ->addLiteral('{')
            ->addPressdown($matches[2])
            ->addLiteral('}')
            ->addLiteral('(');
        if ($matches[3]) {
            $markup->addOption($matches[3]);
        }
        if ($matches[4]) {
            $markup->addOption(($matches[3] ? ' ' : '') . $matches[4]);
        }
        return $markup->addLiteral(')');
    }

    /**
     * @param $pullQuote
     * @return bool
     */
    protected function hasPullQuote($pullQuote)
    {
        return key_exists($pullQuote, $this->pullQuotes);
    }

    /**
     * @param $matches
     * @return string
     * @throws CliPressException
     */
    protected function process($matches)
    {
        $content = $this->parseMarkdown($matches[2], true);
        $float = 'pull-quote-' . (!empty($matches[3]) ? $matches[3] : 'right');
        $quote = "<aside class=\"pull-quote $float\"><span class=\"pull-quote-left-quote\">&ldquo;</span><blockquote>$content</blockquote><span class=\"pull-quote-right-quote\">&rdquo;</span></aside>";
        if (!empty($matches[4])) {
            if (isset($this->pullQuotes[$matches[4]])) {
                throw new CliPressException("Expected unique Pull Quote Anchor name but it already exists: $matches[4].");
            }
            $here = '';
            $this->pullQuotes[$matches[4]] = $quote;
        } else {
            $here = $quote;
        }
        return "$matches[2]$here";
    }
}
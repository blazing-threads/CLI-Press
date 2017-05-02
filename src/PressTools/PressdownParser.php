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

class PressdownParser extends \ParsedownExtra
{
    /**
     * @var array
     */
    protected $blockDirectives;

    /**
     * @var string
     */
    protected $currentCallback;

    /**
     * @var int
     */
    protected $currentFigure = 1;

    /**
     * @var int
     */
    protected $currentTable = 1;

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
    protected $postDirectives;

    /**
     * Array of patterns and callbacks for pre processing directives
     * @var array
     */
    protected $preDirectives;

    /**
     * @var array
     */
    protected $pullQuotes = [];

    /**
     * @var array
     */
    protected $tables = [];

    /**
     * PressdownParser constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->blockDirectives['figure'] = '/(@|)\{=fig-([a-zA-Z0-9_-]+)\s+(.+)(\s+)fig=\}(\((.+)\))??/sUm';
        $this->blockDirectives['classed'] = '/(@|)\{@([abcdflopsux]|fc|blockquote|code|div|figure|figcaption|li|ol|span|ul)-([a-zA-Z-\.]*)\s+(.+)(\s+)\2@\}/sUm';

        $this->preDirectives['fontAwesome'] = '/(@|)\{f@([a-z0-9 -]+)\}/';
        $this->preDirectives['pageBreak'] = '/(@|)\{break}/';
        $this->preDirectives['pullQuote'] = '/(@|)pq\{(.+)\}\((left|right|)\s?([a-zA-Z0-9_-]*)\)/U';
        $this->preDirectives['table'] = '/^(@|)table-(\d+)\{(.+)^\}#([a-zA-Z0-9_a]+)\((.*)\)(?=$)/sUm';

        $this->postDirectives['figureLink'] = '/(@|)f\{(.*)\}\((.+)\)/U';
        $this->postDirectives['pullQuoteAnchor'] = '/(@|)pqa\{([a-zA-Z0-9_-]+)\}/U';
        $this->postDirectives['tableLink'] = '/(@|)t\{(.*)\}\((.+)\)/U';

        $this->finalDirectives['escapedCodeBlocks'] = '/^(\s*@)```/m';
        $this->finalDirectives['escapedTwigExpression'] = '/()\{@\{ (.+) \}\}/';
    }

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
     * @param $pattern
     * @param callable $callback
     * @throws CliPressException
     */
    public function registerDirective($type, $pattern, callable $callback)
    {
        if (!preg_match('/(pre|post|block|final)/', $type)) {
            throw new CliPressException("Expected one of: pre, post, block, final.  Received: " . @(string) $type);
        }

        $type = $type . 'Directives';
        $this->$type[$pattern] = $callback;
    }

    /**
     * @param $matches
     * @return string
     */
    protected function classed($matches)
    {
        $matches[4] = preg_replace_callback($this->blockDirectives['classed'], [$this, 'processDirective'], $matches[4]);
        $matches[4] = $this->stripMarkdownPTags(parent::parse($matches[4]));
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

    /**
     * @param $matches
     * @return string
     */
    protected function escapedTwigExpression($matches)
    {
        return "{{ $matches[2] }}";
    }

    /**
     * @param $matches
     * @return string
     * @throws CliPressException
     */
    protected function figure($matches)
    {
        $matches[3] = preg_replace_callback($this->blockDirectives['figure'], [$this, 'processDirective'], $matches[3]);
        $matches[3] = $this->stripMarkdownPTags(parent::parse($matches[3]));
        if (empty($matches[6])) {
            $caption = '';
            $class = ' class="caption-less"';
            $anchor = '';
        } else {
            $matches[6] = preg_replace_callback($this->blockDirectives['figure'], [$this, 'processDirective'], $matches[6]);
            $matches[6] = $this->stripMarkdownPTags(parent::parse($matches[6]));
            if (key_exists($matches[2], $this->figures)) {
                throw new CliPressException("The Figure Directive must use unique figure names.  The name '$matches[2]' is already defined.");
            }
            $this->figures[$matches[2]] = $this->currentFigure++;
            $caption = "<figcaption>Figure {$this->figures[$matches[2]]}: $matches[6]</figcaption>";
            $class = '';
            // this fakes out wkhtmltopdf so that the /Dest is stored within the PDF object stream
            $anchor = "<a href=\"#figure-$matches[2]\" name=\"figure-$matches[2]\">&nbsp;</a>";
        }
        $captionAbove = app()->instructions()->figureCaptionAbove ? "\n$caption" : '';
        $captionBelow = $captionAbove ? '' : "$caption\n";
        // we wrap it in a div to prevent Markdown from touching it.  Even though we parsed the Markdown above, it will be done again.
        return "<div>$captionAbove<figure$class>$anchor$matches[3]</figure>$captionBelow</div>";
    }

    /**
     * @param $matches
     * @return string
     */
    protected function figureLink($matches)
    {
        if (!key_exists($matches[3], $this->figures)) {
            return '';
        }

        $figure = $this->figures[$matches[3]];
        $text = empty($matches[2]) ? '' : ": $matches[2]";

        return "<a href=\"curator#figure-$matches[3]\">Figure $figure$text</a>";
    }

    /**
     * @param $matches
     * @return string
     */
    protected function fontAwesome($matches)
    {
        $this->hasFA = true;
        $matches = explode(' ', $matches[2]);
        $icon = array_shift($matches);

        $classes = '';
        foreach ($matches as $class) {
            if (preg_match('/^(lg|[2-5]x)|(rotate-(9|18|27)0)|flip-(horizontal|vertical)/', $class)) {
                $classes .= ' fa-' . $class;
            }
        }

        return "<i class=\"fa fa-$icon$classes\"></i>";
    }

    /**
     * @return string
     */
    protected function pageBreak()
    {
        return '<div class="break"></div>';
    }

    /**
     * @param $matches
     * @return string
     */
    protected function processDirective($matches)
    {
        if (!empty($matches[1])) {
            $whitespace = str_replace('@', '', $matches[1]);
            return $whitespace . substr($matches[0], strlen($matches[1]));
        }

        return $this->{$this->currentCallback}($matches);
    }

    /**
     * @param $type
     * @param $markup
     * @return mixed
     */
    protected function processDirectives($type, $markup)
    {
        $directives = $type . 'Directives';
        foreach ($this->$directives as $callback => $pattern) {
            $this->currentCallback = $callback;
            $markup = preg_replace_callback($pattern, [$this, 'processDirective'], $markup, -1, $count);
        }

        return $markup;
    }

    /**
     * @param $matches
     * @return string
     * @throws CliPressException
     */
    protected function pullQuote($matches)
    {
        $content = $this->stripMarkdownPTags(parent::parse($matches[2]));
        $float = 'pull-quote-' . (!empty($matches[3]) ? $matches[3] : 'left');
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

    /**
     * @param $matches
     * @return mixed|string
     */
    protected function pullQuoteAnchor($matches)
    {
        if (!isset($this->pullQuotes[$matches[2]])) {
            return '';
        }

        return $this->processDirectives('block', $this->pullQuotes[$matches[2]]);
    }

    /**
     * @param $markup
     * @return mixed
     */
    protected function stripMarkdownPTags($markup)
    {
        return preg_replace('/<\/?p>/', '', $markup);
    }

    /**
     * @param $matches
     * @return SimpleTable
     */
    protected function table($matches)
    {
        $this->tables[$matches[4]] = $this->currentTable++;

        return new SimpleTable($matches, $this->tables[$matches[4]]);
    }

    /**
     * @param $matches
     * @return string
     */
    protected function tableLink($matches)
    {
        if (!key_exists($matches[3], $this->tables)) {
            return '';
        }

        $table = $this->tables[$matches[3]];
        $text = empty($matches[2]) ? '' : ": $matches[2]";

        return "<a href=\"curator#table-$matches[3]\">Table $table$text</a>";
    }
}
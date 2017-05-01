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

namespace BlazingThreads\CliPress;

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
    protected $currentFigure = 0;

    /**
     * @var array
     */
    protected $figures = [];

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
     * If the last parsed markup contained the Font Awesome directive.
     * @var bool
     */
    protected $hasFA = false;

    public function __construct()
    {
        parent::__construct();
        $this->blockDirectives['figure'] = '/(@|)\{=fig-([a-zA-Z0-9_-]+)\s+(.+)(\s+)fig=\}(\((.+)\))??/sUm';
        $this->blockDirectives['classed'] = '/(@|)\{@([abcdflopsux]|fc)-([a-zA-Z-\.]*)\s+(.+)(\s+)\2@\}/sUm';

        $this->preDirectives['fontAwesome'] = '/(@|)\{f@([a-z0-9 -]+)\}/';
        $this->preDirectives['pageBreak'] = '/(@|)\{break}/';

        $this->postDirectives['escapedCodeBlocks'] = '/^(\s*@)```/m';
        $this->postDirectives['escapedTwigExpression'] = '/()\{@\{ (.+) \}\}/';
    }

    /**
     * @param $markup
     * @return PressedLeaf
     */
    public function parse($markup)
    {
        $markup = $this->processDirectives('pre', $markup);
        $markup = $this->processDirectives('block', $markup);
        $markup = parent::parse($markup);
        $markup = $this->processDirectives('post', $markup);
        $leaf = new PressedLeaf($markup, $this->hasFA);
        $this->hasFA = false;
        return $leaf;
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
                $tag = 'blockquote';
                break;
            case 'c':
                $tag = 'code';
                break;
            case 'f':
                $tag = 'figure';
                break;
            case 'fc':
                $tag = 'figcaption';
                break;
            case 'd':
                $tag = 'div';
                break;
            case 'l':
                $tag = 'li';
                break;
            case 'o':
                $tag = 'ol';
                break;
            case 'p':
                $tag = 'p';
                break;
            case 's':
                $tag = 'span';
                break;
            case 'u':
                $tag = 'ul';
                break;
            default:
                $tag = 'div';
        }
        $class = empty($matches[3]) ? '' : ' class="' . str_replace('.', ' ', $matches[3]) . '"';
        return "<$tag$class>$matches[4]</$tag>";
    }

    protected function escapedTwigExpression($matches)
    {
        return "{{ $matches[2] }}";
    }

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
            $caption = "\n<figcaption>Figure $this->currentFigure: $matches[6]</figcaption>";
            $class = '';
            // this fakes out wkhtmltopdf so that the /Dest is stored within the PDF object stream
            $anchor = "<a href=\"#$matches[2]\" name=\"$matches[2]\">&nbsp;</a>";
        }
        // we wrap it in a div to prevent Markdown from touching it.  Even though we parsed the Markdown above, it will be done again.
        return "<div><figure$class>$anchor$matches[3]</figure>$caption</div>";
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
            if ($callback == 'figure') {
//                var_dump('before', $markup);
            }
            $markup = preg_replace_callback($pattern, [$this, 'processDirective'], $markup, -1, $count);
            if ($callback == 'figure') {
//                var_dump('after', $markup);
            }
        }
        return $markup;
    }

    protected function stripMarkdownPTags($markup)
    {
        return preg_replace('/<\/?p>/', '', $markup);
    }
}
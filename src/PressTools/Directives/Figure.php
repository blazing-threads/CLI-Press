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

class Figure extends BaseDirective
{
    /**
     * @var int
     */
    protected $currentFigure = 1;

    /**
     * @var array
     */
    protected $figures = [];

    /**
     * @var string
     */
    protected $pattern = '/(@|)figure\{(.+)\}\((.*)\)?#([a-zA-Z0-9_-]+?)/sUm';

    /**
     * @param $figure
     * @return bool|mixed
     */
    public function getFigure($figure)
    {
        return $this->hasFigure($figure) ? $this->figures[$figure] : false;
    }

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new ColorCoder();
        return $markup->addDirective('figure')
            ->addLiteral('{')
            ->addPressdown($matches[2])
            ->addLiteral('}(')
            ->addOption($matches[3])
            ->addLiteral(')#')
            ->addOption($matches[4]);
    }

    /**
     * @param $figure
     * @return bool
     */
    protected function hasFigure($figure)
    {
        return key_exists($figure, $this->figures);
    }

    /**
     * @param $matches
     * @return string
     * @throws CliPressException
     */
    protected function process($matches)
    {
        $matches[2] = preg_replace_callback($this->pattern, [$this, 'processDirective'], $matches[2]);
        $matches[2] = $this->parseMarkdown($matches[2]);

        // register the anchor name
        if (key_exists($matches[4], $this->figures)) {
            throw new CliPressException("The Figure Directive must use unique figure names.  The name '$matches[4]' is already defined.");
        }
        $number = $this->currentFigure++;
        $this->figures[$matches[4]] = $number;

        // this fakes out wkhtmltopdf so that the /Dest is stored within the PDF object stream
        $anchor = "<a href=\"#figure-$matches[4]\" name=\"figure-$matches[4]\">&nbsp;</a>";

        // set caption
        if (empty($matches[3])) {
            $caption = '';
            $class = ' class="caption-less"';
        } else {
            $matches[3] = preg_replace_callback($this->pattern, [$this, 'processDirective'], $matches[3]);
            $matches[3] = $this->parseMarkdown($matches[3]);
            $caption = "<figcaption>Figure $number: $matches[3]</figcaption>";
            $class = '';
        }
        $captionAbove = app()->instructions()->figureCaptionAbove ? "\n$caption" : '';
        $captionBelow = $captionAbove ? '' : "$caption\n";

        // we wrap it in a div to prevent Markdown from touching it.  Even though we parsed the Markdown above, it will be done again.
        return "<div>$captionAbove<figure$class>$anchor$matches[2]</figure>$captionBelow</div>";
    }
}
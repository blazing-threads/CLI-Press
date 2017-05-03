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

class SimpleTable
{
    /**
     * @var string
     */
    protected $caption;

    /**
     * @var int
     */
    protected $columnCount;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $currentSectionType;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var $markup
     */
    protected $markup;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var PressdownParser
     */
    protected $parser;

    /**
     * SimpleTable constructor.
     * @param $matches
     * @param $label
     */
    public function __construct($matches, $label)
    {
        $this->columnCount = $matches[2];
        $this->content = $matches[3];
        $this->caption = $matches[4];
        $this->name = $matches[5];
        $this->label = $label;
        $this->buildMarkup();
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->markup;
    }

    /**
     * @param $markup
     */
    protected function addMarkup($markup)
    {
        $this->markup .= $markup;
    }

    /**
     * @param $type
     * @param $content
     */
    protected function addRow($type, $content)
    {
        $this->addMarkup("\t\t<tr>\n");
        $columns = preg_split('/^\s+(\d+)\./m', $content, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
        $lastColumn = 0;
        while(null !== $currentColumn = array_shift($columns)) {
            $columnSpan = (1 === $difference = min($currentColumn, $this->columnCount) - $lastColumn) ? '' : " colspan=\"$difference\"";
            $markup = $this->parser->parseMarkdown(trim(array_shift($columns)));
            $this->addMarkup("\t\t\t<$type$columnSpan>$markup</$type>\n");
            $lastColumn = $currentColumn;
        }
        $this->addMarkup("\t\t</tr>\n");
    }

    /**
     *
     */
    protected function buildMarkup()
    {
        if (empty($this->parser)) {
            $this->parser = app()->make(PressdownParser::class);
        }

        $this->addMarkup("<table class=\"table table-striped\">\n");

        if (!empty($this->caption)) {
            $caption = $this->parser->parseMarkdown($this->caption, true);
            $this->addMarkup("\t<caption>\n<a href=\"#table-$this->name\" name=\"table-$this->name\">Table $this->label : $caption</a>\n</caption>\n");
        }

        $rows = preg_split('/^\s+([hrf]):/m', $this->content, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
        while(null !== $value = array_shift($rows)) {
            switch ($value) {
                case 'f':
                    $section = 'tfoot';
                    $type = 'td';
                    break;
                case 'h':
                    $section = 'thead';
                    $type = 'th';
                    break;
                case 'r':
                default:
                    $section = 'tbody';
                    $type = 'td';
            }
            $this->startSection($section);
            $this->addRow($type, array_shift($rows));
        }

        $this->endSection();
        $this->addMarkup('</table>');
    }

    /**
     *
     */
    protected function endSection()
    {
        $this->addMarkup("\t</$this->currentSectionType>\n");
    }

    /**
     * @param $type
     * @return bool
     */
    protected function inSection($type)
    {
        return $this->currentSectionType === $type;
    }

    /**
     * @param $type
     */
    protected function startSection($type)
    {
        if ($this->inSection($type)) {
            return;
        }

        if (!$this->inSection(null)) {
            $this->endSection();
        }

        $this->currentSectionType = $type;

        $this->addMarkup("\t<$type>\n");
    }
}
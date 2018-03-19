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
use BlazingThreads\CliPress\PressTools\SimpleTable;

class Table extends BaseDirective
{
    /**
     * @var int
     */
    protected $currentTable = 1;

    /**
     * @var array
     */
    protected $tables = [];

    /**
     * @var string
     */
    protected $pattern = '/^(@|)table-(\d+)\{(.+)^\}\(([^\n]*?)\)#([a-zA-Z0-9_a-]+?)/sUm';

    /**
     * @param $table
     * @return bool|mixed
     */
    public function getTable($table)
    {
        return $this->hasTable($table) ? $this->tables[$table] : false;
    }

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        $markup->addLiteral('table');
        return $markup;
    }

    /**
     * @param $table
     * @return bool
     */
    protected function hasTable($table)
    {
        return key_exists($table, $this->tables);
    }

    /**
     * @param $matches
     * @return string
     * @throws CliPressException
     */
    protected function process($matches)
    {
        if (isset($this->tables[$matches[5]])) {
            throw new CliPressException("The Table Directive must use unique table names. The name '$matches[5]' is already defined.'");
        }

        $label = empty($matches[4]) ? '' : $this->currentTable++;
        $this->tables[$matches[5]] = [$label, $matches[4]];
        return new SimpleTable($matches, $label);
    }
}
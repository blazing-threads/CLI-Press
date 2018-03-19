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

class TableLink extends BaseDirective
{
    /**
     * @var string
     */
    protected $pattern = '/(@|)t\{(.*)\}\((.+)\)/U';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        return $markup->addDirective('t')
            ->addLiteral('{')
            ->addPressdown($matches[2])
            ->addLiteral('}(')
            ->addOption($matches[3])
            ->addLiteral(')');
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        if (! $table = app()->make(Table::class)->getTable($matches[3])) {
            return '';
        }

        if (!empty($matches[2])) {
            $caption = $matches[2];
        } else {
            $caption = empty($table[0]) ? 'Table' : "Table $table[0]: $table[1]";
        }

        return "<a href=\"curator#table-$matches[3]\">$caption</a>";
    }
}
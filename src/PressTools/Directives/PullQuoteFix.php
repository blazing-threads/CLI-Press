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

class PullQuoteFix extends BaseDirective
{
    /**
     * @var bool
     */
    protected $neverEscape = true;

    /**
     * @var string
     */
    protected $pattern = '/(<p>[^\n\r\f]*)(<aside class="pull-quote[^\"]*(right|left)">.*<\/aside>)(.*<\/p>)/sUm';

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        // this should never be called
        return 'bad juju';
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        return $matches[3] == 'right' ? "$matches[1]$matches[4]$matches[2]" : "$matches[2]$matches[1]$matches[4]";
    }
}
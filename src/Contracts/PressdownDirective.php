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


namespace BlazingThreads\CliPress\Contracts;


interface PressdownDirective
{
    static function parse($markup);
}
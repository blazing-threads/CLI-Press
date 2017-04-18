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

class PressedLeaf
{
    protected $content;

    protected $withFA;

    public function __construct($content, $withFA)
    {
        $this->content = $content;
        $this->withFA = $withFA;
    }

    public function __toString()
    {
        return $this->content;
    }

    public function hasFA()
    {
        return $this->withFA;
    }
}
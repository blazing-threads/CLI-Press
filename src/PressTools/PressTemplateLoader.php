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

class PressTemplateLoader extends \Twig_Loader_Filesystem
{
    /**
     * @param $name
     * @return bool|string
     */
    public function findTemplate($name)
    {
        return parent::findTemplate($name);
    }
}
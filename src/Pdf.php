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

use mikehaertl\wkhtmlto\Pdf as BasePdf;

class Pdf extends BasePdf
{
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setOptions(['cache-dir' => $this->getCacheDir()]);
    }

    protected function getCacheDir()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cli-press-web-cache';
        if (!file_exists($dir) && !mkdir($dir)) {
            throw new CliPressException("Failed to make cache dir: $dir.");
        }

        return $dir;
    }
}
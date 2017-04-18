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

namespace BlazingThreads\CliPress\Managers;

use iio\libmergepdf\Merger;

class LeafManager
{

    /**
     * PDFs to merge
     * @var array
     */
    protected $files = [];

    /**
     * @param $file
     */
    public function addFile($file)
    {
        array_unshift($this->files, $file);
    }

    /**
     * @param $filename
     * @return int
     */
    public function merge($filename)
    {
        $merger = new Merger();
        foreach ($this->files as $file) {
            $merger->addFromFile($file);
        }
        return file_put_contents($filename, $merger->merge());
    }
}
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

use BlazingThreads\PdfCurator\Curator;

class LeafManager
{
    /**
     * @var Curator
     */
    protected $curator;

    public function __construct()
    {
        $this->curator = new Curator();
    }

    /**
     * @param $file
     * @param bool $prepend
     */
    public function addFile($file, $prepend = false)
    {
        $this->curator->addFile($file, $prepend);
    }

    /**
     * @param $file
     * @return int
     */
    public function getPageCount($file)
    {
        return $this->curator->getPageCount($file);
    }

    /**
     * @param $filename
     * @return int
     */
    public function merge($filename)
    {
        return file_put_contents($filename, $this->curator->merge());
    }

    /**
     * @param $file
     */
    public function prependFile($file)
    {
       $this->curator->prependFile($file);
    }

    /**
     *
     */
    public function restart()
    {
        unset($this->curator);

        $this->curator = new Curator();
    }
}
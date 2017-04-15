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

use Twig_Environment;
use Twig_Loader_Filesystem;

class TemplateManager
{
    protected $loader;

    protected $twig;

    public function __construct()
    {
        $this->loader = new Twig_Loader_Filesystem('themes', __DIR__ . '/..');
        $this->twig = new Twig_Environment($this->loader, array(
            'cache' => false
        ));
    }

    public function render($template, $variables)
    {
        return $this->twig->render($template, $variables);
    }
}
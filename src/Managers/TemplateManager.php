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
use Twig_Loader_Array;
use Twig_Loader_Filesystem;

class TemplateManager
{
    /**
     * @var Twig_Loader_Filesystem
     */
    protected $loader;

    /**
     * @var Twig_Loader_Array
     */
    protected $stringLoader;

    /**
     * @var Twig_Environment
     */
    protected $stringTwig;

    /**
     * @var Twig_Environment
     */
    protected $twig;

    public function __construct()
    {
        $this->loader = new Twig_Loader_Filesystem();
        // register themes in the order of priority: personal, system, built-in
        foreach (['personal', 'system'] as $key) {
            if ($path = app()->config()->get("themes.$key")) {
                $this->loader->addPath($path);
            }
        }
        $this->loadDocumentThemes();
        $this->loader->addPath(app()->path('themes.built-in'));

        $this->twig = new Twig_Environment($this->loader, [
            'cache' => false,
            'autoescape' => false,
        ]);

        $this->stringLoader = new Twig_Loader_Array(['string' => '']);

        $this->stringTwig = new Twig_Environment($this->stringLoader, [
            'cache' => false,
            'autoescape' => false,
        ]);
    }

    /**
     * @param $template
     * @param array $variables
     * @return string
     */
    public function render($template, $variables = [])
    {
        return $this->twig->render($template, $variables);
    }

    /**
     * @param $string
     * @param array $variables
     * @return string
     */
    public function renderString($string, $variables = [])
    {
        $this->stringLoader->setTemplate('string', $string);
        return $this->stringTwig->render('string', $variables);
    }

    /**
     * @param $template
     * @return bool
     */
    public function themeHasFile($template)
    {
        return $this->loader->exists($template);
    }

    /**
     *
     */
    protected function loadDocumentThemes()
    {
        $documentConfig = @file_get_contents(app()->path('press-root', 'cli-press.json'));
        if (! $documentConfig = json_decode($documentConfig, true)) {
            return;
        }

        if (!isset($documentConfig['document-themes']) || !is_dir(app()->path('press-root', $documentConfig['document-themes']))) {
            return;
        }

        $this->loader->addPath(app()->path('press-root', $documentConfig['document-themes']));
    }
}
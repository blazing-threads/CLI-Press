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

use BlazingThreads\CliPress\PressTools\PressInstructionStack;
use BlazingThreads\CliPress\PressTools\PressTemplateLoader;
use Twig_Environment;
use Twig_Function;
use Twig_Loader_Array;
use Twig_Loader_Filesystem;

class TemplateManager
{
    /**
     * @var Twig_Loader_Filesystem
     */
    protected $loader;

    /**
     * @var array
     */
    protected $namespaces = ['document', 'personal', 'system'];

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
        $this->loader = new PressTemplateLoader();
        // register themes in the order of priority: document, personal, system, built-in
        $this->loadDocumentThemes();
        foreach (['personal', 'system'] as $key) {
            if ($path = app()->config()->get("themes.$key")) {
                $this->loader->addPath($path, $key);
            }
        }
        $this->loader->addPath(app()->path('themes.built-in'), 'built-in');

        $function = new \Twig_SimpleFunction('customAsset', [PressInstructionStack::class, 'customAsset']);

        $this->twig = new Twig_Environment($this->loader, [
            'cache' => false,
            'autoescape' => false,
        ]);

        $this->twig->addFunction($function);

        $this->stringLoader = new Twig_Loader_Array(['string' => '']);

        $this->stringTwig = new Twig_Environment($this->stringLoader, [
            'cache' => false,
            'autoescape' => false,
        ]);

        $this->stringTwig->addFunction($function);
    }

    /**
     * @param $template
     * @return array|string
     */
    public function getAllExistingPaths($template)
    {
        $paths = [];

        foreach (array_reverse($this->namespaces) as $namespace) {
            if ($this->loader->exists($path = "@$namespace/$template")) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @param $template
     * @return bool|string
     */
    public function getFilePath($template)
    {
        return $this->loader->findTemplate($template);
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
     * @param $template
     * @param array $variables
     * @return string
     */
    public function renderBuiltIn($template, $variables = [])
    {
        return $this->twig->render("@built-in/{$this->getBuiltInFileName($template)}", $variables);
    }

    /**
     * @param $template
     * @param array $variables
     * @return string
     */
    public function renderByPath($template, $variables = [])
    {
        return $this->twig->render($template, $variables);
    }

    /**
     * @param $template
     * @param array $variables
     * @return string
     */
    public function renderCascade($template, $variables = [])
    {
        $content = $this->renderBuiltIn($template, $variables);

        foreach (array_reverse($this->namespaces) as $namespace) {
            if ($this->loader->exists("@$namespace/$template")) {
                $content .= $this->twig->render("@$namespace/$template", $variables) . "\n";
            }
        }

        return $content;
    }

    /**
     * @param $template
     * @param array $variables
     * @return string
     */
    public function renderFirst($template, $variables = [])
    {
        foreach ($this->namespaces as $namespace) {
            if ($this->loader->exists("@$namespace/$template")) {
                return $this->twig->render("@$namespace/$template", $variables);
            }
        }

        return $this->renderBuiltIn($template, $variables);
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
        foreach ($this->namespaces as $namespace) {
            if ($this->loader->exists("@$namespace/$template")) {
                return true;
            }
        }

        return $this->loader->exists("@built-in/{$this->getBuiltInFileName($template)}");
    }


    /**
     * @param $template
     * @return string
     */
    protected function getBuiltInFileName($template)
    {
        return 'cli-press/' . array_slice(explode('/', $template), -1)[0];
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

        $this->loader->addPath(app()->path('press-root', $documentConfig['document-themes']), 'document');
    }
}
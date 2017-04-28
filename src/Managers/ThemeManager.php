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

use BlazingThreads\CliPress\PressConsole;

class ThemeManager
{
    /** @var PressConsole */
    protected $console;

    /** @var array */
    protected $presetCache = [];

    /** @var TemplateManager */
    protected $templateManager;

    /** @var array */
    protected $templateVariables = [];

    /** @var string */
    protected $theme = 'cli-press';

    /** @var array */
    protected $themeDefaultsCache = [];

    /**
     * ThemeManager constructor.
     * @param TemplateManager $templateManager
     * @param PressConsole $console
     */
    public function __construct(TemplateManager $templateManager, PressConsole $console)
    {
        $this->templateManager = $templateManager;
        $this->console = $console;
    }

    /**
     * Find the first theme file defined for the given file.
     * If no theme defines the file, use the base theme.
     * @param $file
     * @param array $variables
     * @return string
     */
    public function getFirstFile($file, $variables = [])
    {
        $themeFile = empty($this->theme) ? false : $this->getThemePath($file, $this->theme);
        return $this->getThemeFile($file, $variables, $themeFile === false || !$this->templateManager->themeHasFile($themeFile));
    }

    /**
     * @param $preset
     * @param $theme
     * @return array|mixed
     */
    public function getPreset($preset, $theme)
    {
        if (key_exists($theme, $this->presetCache) && key_exists($preset, $this->presetCache[$theme])) {
            return $this->presetCache[$theme][$preset];
        }

        $presetFile = "$preset.preset.json";

        $settings = [];

        if ($this->templateManager->themeHasFile($this->getThemePath($presetFile, $theme))) {
            $settings = $this->jsonOrEmptyArray($this->getThemeFile($presetFile, [], false, $theme));
        } elseif ($this->templateManager->themeHasFile($presetFile)) {
            $settings = $this->jsonOrEmptyArray($this->templateManager->render($presetFile));
        }

        $this->presetCache[$theme][$preset] = $settings;

        return $settings;
    }

    /**
     * @param $theme
     * @return array|mixed
     */
    public function getThemeDefaults($theme)
    {
        if (key_exists($theme, $this->themeDefaultsCache)) {
            return $this->themeDefaultsCache[$theme];
        }

        $defaultsPath = $this->getThemePath('cli-press.json', $theme);

        $defaults = [];
        if ($this->templateManager->themeHasFile($defaultsPath)) {
            $defaults = $this->jsonOrEmptyArray($this->getThemeFile('cli-press.json', [], false, $theme));
        }

        $this->themeDefaultsCache[$theme] = $defaults;

        return $defaults;
    }

    /**
     * @param $file
     * @param array $variables
     * @param bool $baseTheme
     * @param null $forceTheme
     * @return string
     */
    public function getThemeFile($file, $variables = [], $baseTheme = false, $forceTheme = null)
    {
        if (!$baseTheme && (empty($this->theme) || !$this->templateManager->themeHasFile($this->getThemePath($file, $this->theme)))) {
            return '';
        }
        $filename = $this->getThemePath($file, !$baseTheme ? ($forceTheme ?: $this->theme) : 'cli-press');

        $this->console->veryVerbose("rendering $filename");

        return $this->templateManager->render($filename, array_merge($variables, $this->templateVariables));
    }

    /**
     * @param $file
     * @param $theme
     * @return string
     */
    public function getThemePath($file, $theme)
    {
        return "$theme/$file";
    }

    /**
     * @param $theme
     * @param array $templateVariables
     */
    public function setThemeAndTemplateVariables($theme, array $templateVariables)
    {
        $this->theme = $theme;
        $this->templateVariables = $templateVariables;
    }

    /**
     * @param $json
     * @return array|mixed
     */
    protected function jsonOrEmptyArray($json)
    {
        $json = json_decode($json, true);
        return json_last_error() === JSON_ERROR_NONE ? $json : [];
    }
}
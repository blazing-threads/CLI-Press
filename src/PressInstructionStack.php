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

use BlazingThreads\CliPress\Managers\ThemeManager;

/**
 * Class PressInstructionStack
 * @package BlazingThreads\CliPress
 *
 * @property $autoPageBreak
 * @property $bookToc
 * @property $borderColorAll
 * @property $borderColorChapterCoverFooter
 * @property $borderColorChapterCoverHeader
 * @property $borderColorFooter
 * @property $borderColorHeader
 * @property $borderColorRootCoverFooter
 * @property $borderColorRootCoverHeader
 * @property $borderColorToc
 * @property $chapterStartsAfterEmptyPage
 * @property $chapterStartsRight
 * @property $chapterTitle
 * @property $constants
 * @property $coverType
 * @property $extensions
 * @property $filename
 * @property $fontFamilyBlockQuoteCode
 * @property $fontFamilyBody
 * @property $fontFamilyChapterCoverAll
 * @property $fontFamilyChapterCover
 * @property $fontFamilyChapterCoverFooter
 * @property $fontFamilyChapterCoverHeader
 * @property $fontFamilyCodeHighlighting
 * @property $fontFamilyRootCoverAll
 * @property $fontFamilyRootCover
 * @property $fontFamilyRootCoverFooter
 * @property $fontFamilyRootCoverHeader
 * @property $fontFamilyToc
 * @property $footerEvenOdd
 * @property $hasCover
 * @property $hasRootCover
 * @property $headerEvenOdd
 * @property $ignore
 * @property $linkColorAll
 * @property $linkColorBody
 * @property $linkColorTocPrimary
 * @property $linkColorTocSecondary
 * @property $logoAll
 * @property $logoChapterCover
 * @property $logoChapterCoverFooter
 * @property $logoChapterCoverHeader
 * @property $logoFooter
 * @property $logoRootCover
 * @property $logoRootCoverFooter
 * @property $logoRootCoverHeader
 * @property $logoHeader
 * @property $presets
 * @property $pressTime
 * @property $simpleRootCover
 * @property $theme
 * @property $title
 * @property $useChapterCovers
 */
class PressInstructionStack
{
    protected $instructions;

    protected $pressDirectory;

    protected $stack = [];

    protected $templateVariables = [
        'border-color-all', 'border-color-chapter-cover-footer', 'border-color-chapter-cover-header', 'border-color-footer', 'border-color-header', 'border-color-toc',

        'font-family-block-quote-code', 'font-family-body', 'font-family-code-highlighting', 'font-family-footer', 'font-family-header', 'font-family-toc',
        'font-family-chapter-cover-all', 'font-family-chapter-cover', 'font-family-chapter-cover-footer', 'font-family-chapter-cover-header',
        'font-family-root-cover-all', 'font-family-root-cover', 'font-family-root-cover-footer', 'font-family-root-cover-header',

        'link-color-all', 'link-color-body', 'link-color-toc-primary', 'link-color-toc-secondary',

        'logo-all', 'logo-footer', 'logo-header',
        'logo-chapter-cover', 'logo-chapter-cover-footer', 'logo-chapter-cover-header',
        'logo-root-cover', 'logo-root-cover-footer', 'logo-root-cover-header',

        'chapter-title', 'footer-even-odd', 'header-even-odd', 'press-time', 'title',
    ];

    /** @var ThemeManager */
    protected $themeManager;

    /** @var array */
    protected $validSettings;

    /**
     * PressInstructionStack constructor.
     * @param ThemeManager $themeManager
     */
    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
        $this->pressDirectory = app()->path('press-root');

        $this->validSettings = $this->themeManager->getThemeDefaults('cli-press');
        $this->validSettings['logo-all'] = addcslashes('file://' . app()->path('base', 'src/assets/logo.png'), '/');

        $instructions = $this->validSettings;
        $instructions['press-time'] = date('Y-m-d H:i:s');
        $instructions['title'] = basename($this->pressDirectory);

        $this->instructions = $this->merge($instructions, []);

        $this->themeManager->setThemeAndTemplateVariables($this->instructions['theme'], $this->getTemplateVariables());
    }

    /**
     * @return array
     */
    public function getIgnored()
    {
        $ignored = [];

        $stack = $this->stack;
        $stack[] = $this->instructions;

        foreach ($stack as $config) {
            foreach ($config['ignore'] as $path) {
                if ($path[0] === '!') {
                    unset($ignored[substr($path, 1)]);
                } else {
                    $ignored[$path] = str_replace('*', '\\.*', $path);
                }
            }
        }

        return $ignored;
    }

    /**
     * @param array $variables
     * @return array
     */
    public function getTemplateVariables($variables = [])
    {
        foreach ($this->templateVariables as $variable) {
            $variables[camelCase($variable)] = $this->instructions[$variable];
        }

        return array_merge($variables, $this->instructions['constants'], ['__assetPath' => app()->path('base', 'src/assets')]);
    }

    /**
     *
     */
    public function pop()
    {
        $this->instructions = array_pop($this->stack);
        $this->themeManager->setThemeAndTemplateVariables($this->instructions['theme'], $this->getTemplateVariables());
    }

    /**
     * @param $instructions
     * @param $pressDirectory
     */
    public function stack($instructions, $pressDirectory)
    {
        $this->stack[] = $this->instructions;
        $this->pressDirectory = $pressDirectory;

        $this->instructions = $this->merge($this->instructions, $this->cleanInstructions($instructions));

        if (empty($this->instructions['filename'])) {
            $this->instructions['filename'] = preg_replace('/[^\w\._-]/', '-', $this->instructions['title']);
        }

        if (!key_exists('constants', $this->instructions)) {
            $this->instructions['constants'] = [];
        }

        $this->validateConfiguration();

        $this->themeManager->setThemeAndTemplateVariables($this->instructions['theme'], $this->getTemplateVariables());
    }

    /**
     * @param $property
     * @return mixed|null
     */
    public function __get($property)
    {
        $property = kebabCase($property);
        return key_exists($property, $this->instructions)
            ? $this->instructions[$property]
            : null;
    }

    /**
     * @param $property
     * @return bool
     */
    public function __isset($property)
    {
        $property = kebabCase($property);
        return key_exists($property, $this->instructions);
    }

    /**
     * @param $property
     * @param $value
     */
    public function __set($property, $value)
    {
        $property = kebabCase($property);
        if (key_exists($property, $this->instructions)) {
            $this->instructions[$property] = $value;
        } else {
            var_dump("$property does not exist");
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        ksort($this->instructions);
        return json_encode($this->instructions, JSON_PRETTY_PRINT);
    }

    /**
     * @param $instructions
     * @return array
     */
    protected function cleanInstructions($instructions)
    {
        return array_intersect_key($instructions, $this->validSettings);
    }

    /**
     * @param $presets
     * @param $theme
     * @return array
     */
    protected function getPresets($presets, $theme)
    {
        $presets = (array) $presets;

        $settings = [];

        foreach ($presets as $preset) {
            $settings = array_merge($settings, $this->themeManager->getPreset($preset, $theme));
        }

        return $settings;
    }

    /**
     * @param array $base
     * @param array $overlay
     * @return array
     */
    protected function merge(array $base, array $overlay)
    {
        $result = [];

        $themeDefaults = !key_exists('theme', $overlay)
            ? []
            : $this->cleanInstructions($this->themeManager->getThemeDefaults($overlay['theme']));

        $overlayPreset = !key_exists('presets', $overlay) ? [] : $this->getPresets($overlay['presets'], key_exists('theme', $overlay) ? $overlay['theme'] : $base['theme']);
        $themePreset = !key_exists('presets', $themeDefaults) ? [] : $this->getPresets($themeDefaults['presets'], key_exists('theme', $overlay) ? $overlay['theme'] : $base['theme']);
        $basePreset = !key_exists('presets', $base) ? [] : $this->getPresets($base['presets'], $base['theme']);

        if (key_exists('ignore', $overlay)) {
            $result['ignore'] = $overlay['ignore'];
        } elseif (key_exists('ignore', $overlayPreset)) {
            $result['ignore'] = $overlayPreset['ignore'];
        } elseif (key_exists('ignore', $themeDefaults)) {
            $result['ignore'] = $themeDefaults['ignore'];
        } elseif (key_exists('ignore', $themePreset)) {
            $result['ignore'] = $themePreset['ignore'];
        } elseif (key_exists('ignore', $base)) {
            $result['ignore'] = $base['ignore'];
        } elseif (key_exists('ignore', $basePreset)) {
            $result['ignore'] = $basePreset['ignore'];
        } else {
            $result['ignore'] = [];
        }

        unset($base['ignore'], $basePreset['ignore'], $overlay['ignore'], $overlayPreset['ignore'], $themeDefaults['ignore'], $themePreset['ignore']);

        $result['constants'] = array_merge(
            key_exists('constants', $basePreset) ? $basePreset['constants'] : [],
            key_exists('constants', $base) ? $base['constants'] : [],
            key_exists('constants', $themePreset) ? $themePreset['constants'] : [],
            key_exists('constants', $themeDefaults) ? $themeDefaults['constants'] : [],
            key_exists('constants', $overlayPreset) ? $overlayPreset['constants'] : [],
            key_exists('constants', $overlay) ? $overlay['constants'] : []
        );

        unset($base['constants'], $basePreset['constants'], $overlay['constants'], $overlayPreset['constants'], $themeDefaults['constants'], $themePreset['constants']);

        if (!key_exists('chapter-title', $overlay) && !key_exists('chapter-title', $overlayPreset)
            && !key_exists('chapter-title', $themeDefaults) && !key_exists('chapter-title', $themePreset)
            && !key_exists('chapter-title', $basePreset)
        ) {
            $overlay['chapter-title'] = basename($this->pressDirectory);
        }

        return array_merge($result, $basePreset, $base, $themePreset, $themeDefaults, $overlayPreset, $overlay);
    }

    /**
     * @throws CliPressException
     */
    protected function validateConfiguration()
    {
        if (empty($this->instructions['extensions']) || !is_array($this->instructions['extensions'])) {
            throw new CliPressException('Press instruction "extensions" must be a non-empty array');
        }

        if (!key_exists('constants', $this->instructions) || !is_array($this->instructions['constants'])) {
            throw new CliPressException('Press instruction "constants" must be an associative array of name => value pairs');
        }
    }
}
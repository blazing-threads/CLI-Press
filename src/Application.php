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

use BlazingThreads\CliPress\Commands\Batch;
use BlazingThreads\CliPress\Commands\Configure;
use BlazingThreads\CliPress\Commands\Generate;
use BlazingThreads\CliPress\Commands\Themes;
use BlazingThreads\CliPress\Managers\ConfigurationManager;
use BlazingThreads\CliPress\Managers\LeafManager;
use BlazingThreads\CliPress\Managers\PressManager;
use BlazingThreads\CliPress\Managers\TemplateManager;
use BlazingThreads\CliPress\Managers\ThemeManager;
use BlazingThreads\CliPress\PressTools\Directives\Alert;
use BlazingThreads\CliPress\PressTools\Directives\ClassedBlock;
use BlazingThreads\CliPress\PressTools\Directives\Custom;
use BlazingThreads\CliPress\PressTools\Directives\EscapedCodeBlock;
use BlazingThreads\CliPress\PressTools\Directives\EscapedTwigExpression;
use BlazingThreads\CliPress\PressTools\Directives\Figure;
use BlazingThreads\CliPress\PressTools\Directives\FigureLink;
use BlazingThreads\CliPress\PressTools\Directives\FontAwesome;
use BlazingThreads\CliPress\PressTools\Directives\HeaderLink;
use BlazingThreads\CliPress\PressTools\Directives\HighlightedComment;
use BlazingThreads\CliPress\PressTools\Directives\Keywords;
use BlazingThreads\CliPress\PressTools\Directives\PageBreak;
use BlazingThreads\CliPress\PressTools\Directives\PullQuote;
use BlazingThreads\CliPress\PressTools\Directives\PullQuoteAnchor;
use BlazingThreads\CliPress\PressTools\Directives\PullQuoteFix;
use BlazingThreads\CliPress\PressTools\Directives\Table;
use BlazingThreads\CliPress\PressTools\Directives\TableAbstract;
use BlazingThreads\CliPress\PressTools\Directives\TableLink;
use BlazingThreads\CliPress\PressTools\PressConsole;
use BlazingThreads\CliPress\PressTools\PressdownParser;
use BlazingThreads\CliPress\PressTools\PressInstructionStack;
use Illuminate\Container\Container;
use Symfony\Component\Console\Application as Commander;

class Application extends Container
{

    /**
     * @param string $projectPath
     * @param string $basePath
     */
    public function bootstrap($projectPath, $basePath)
    {
        // set some paths
        $this['path.project'] = $projectPath;
        $this['path.base'] = $basePath;
        $this['path.config'] = $this->getConfigurationDir();
        $this['path.themes.built-in'] = $basePath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Themes';
        $this['path.assets'] = $basePath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'assets';
        $this['path.press-root'] = getcwd();

        // register singletons
        $this->singleton(PressManager::class);
        $this->singleton(ConfigurationManager::class);
        $this->singleton(TemplateManager::class);
        $this->singleton(ThemeManager::class);
        $this->singleton(LeafManager::class);
        $this->singleton(PressConsole::class);
        $this->singleton(Commander::class);
        $this->singleton(PressInstructionStack::class);
        $this->singleton(PullQuote::class);
        $this->singleton(Figure::class);
        $this->singleton(Table::class);
        $this->singleton(Custom::class);

        // tag all commands
        $this->tag(Configure::class, ['command', 'command.configure']);
        $this->tag(Themes::class, ['command', 'command.themes']);
        $this->tag(Generate::class, ['command', 'command.generate']);
        $this->tag(Batch::class, ['command', 'command.batch']);

        // register the cleanup script
        register_shutdown_function([$this, 'cleanup']);

        // define the Pressdown directive registration callback
        $this->resolving(PressdownParser::class, function(PressdownParser $pressdown, Application $app) {
            $pressdown->registerDirective('pre', new Keywords());
            $pressdown->registerDirective('pre', new FontAwesome());
            $pressdown->registerDirective('pre', new PageBreak());
            $pressdown->registerDirective('pre', new HeaderLink(app()->make(PressInstructionStack::class)));
            $pressdown->registerDirective('final', new HighlightedComment());

            $pressdown->registerDirective('block', new ClassedBlock());
            $pressdown->registerDirective('block', $app->make(PullQuote::class));
            $pressdown->registerDirective('block', new Alert());
            $pressdown->registerDirective('block', $app->make(Table::class));
            $pressdown->registerDirective('block', $app->make(Figure::class));
            $pressdown->registerDirective('block', $app->make(Custom::class));

            $pressdown->registerDirective('post', new FigureLink());
            $pressdown->registerDirective('post', new PullQuoteAnchor());
            $pressdown->registerDirective('post', new TableLink());

            $pressdown->registerDirective('final', new EscapedCodeBlock());
            $pressdown->registerDirective('final', new EscapedTwigExpression());
            $pressdown->registerDirective('final', new TableAbstract());
            $pressdown->registerDirective('final', new PullQuoteFix());
        });
    }

    /**
     * Clean up after the application run
     */
    public function cleanup()
    {
        if ($this->bound('path.working')) {
            $this->cleanDirectory($this['path.working']);
        }
    }

    /**
     * @return ConfigurationManager
     */
    public function config()
    {
        return $this->resolve(ConfigurationManager::class);
    }

    /**
     * @return PressInstructionStack
     */
    public function instructions()
    {
        return $this->resolve(PressInstructionStack::class);
    }

    /**
     * Grab the first item tagged with the given label.
     * @param $label
     * @return mixed|null
     */
    public function labeled($label)
    {
        $tagged = $this->tagged($label);
        return empty($tagged) ? null : $tagged[0];
    }

    /**
     * @param $type
     * @param string $path
     * @return string
     * @throws CliPressException
     */
    public function path($type, $path = '')
    {
        $key = 'path.' . @(string) $type;
        if (!$this->bound($key)) {
            throw new CliPressException('Unknown path type: ' . @(string) $type);
        }
        return $this[$key] . (empty($path) ? '' : DIRECTORY_SEPARATOR . @(string) $path);
    }

    /**
     * @param $directory
     */
    protected function cleanDirectory($directory)
    {
        $this->make(PressConsole::class)->veryVerbose("<warn>Cleaning directory: $directory</warn>");

        foreach(glob($directory . DIRECTORY_SEPARATOR . '*') as $path) {
            if (is_dir($path)) {
                $this->cleanDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }

    /**
     * This was lifted from source code for composer.
     * See https://github.com/composer/composer/blob/1.4.0/src/Composer/Factory.php
     * @return string
     */
    protected function getConfigurationDir()
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            if (!getenv('APPDATA')) {
                throw new \RuntimeException('The APPDATA environment variable must be set for CLI Press to locate configuration data.');
            }
            return rtrim(strtr(getenv('APPDATA'), '\\', '/'), '/') . '/CLI-Press';
        }

        $userDir = getenv('HOME');
        if (!$userDir) {
            throw new \RuntimeException('The HOME environment variable must be set for CLI Press to locate configuration data.');
        }

        $userDir = rtrim(strtr($userDir, '\\', '/'), '/');

        if (is_dir($userDir . '/.cli-press')) {
            return $userDir . '/.cli-press';
        }

        if ($this->useXdg()) {
            // XDG Base Directory Specifications
            $xdgConfig = getenv('XDG_CONFIG_HOME') ?: $userDir . '/.config';
            return $xdgConfig . '/cli-press';
        }

        return $userDir . '/.cli-press';
    }

    /**
     * This was lifted from source code for composer.
     * See https://github.com/composer/composer/blob/1.4.0/src/Composer/Factory.php     * @return bool
     */
    protected function useXdg()
    {
        foreach (array_keys($_SERVER) as $key) {
            if (substr($key, 0, 4) === 'XDG_') {
                return true;
            }
        }

        return false;
    }

}
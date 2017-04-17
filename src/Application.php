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

use BlazingThreads\CliPress\Commands\Configure;
use BlazingThreads\CliPress\Commands\Generate;
use BlazingThreads\CliPress\Commands\Themes;
use BlazingThreads\CliPress\Managers\ConfigurationManager;
use BlazingThreads\CliPress\Managers\LeafManager;
use BlazingThreads\CliPress\Managers\TemplateManager;
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
        // set some paths for retrieval later
        $this['path.project'] = $projectPath;
        $this['path.base'] = $basePath;
        $this['path.themes.builtin'] = $basePath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Themes';
        $this['path.config'] = $this->getConfigurationDir();

        // register our managers and commander
        $this->singleton('managers.configuration', ConfigurationManager::class);
        $this->singleton('managers.template', TemplateManager::class);
        $this->singleton('managers.leaf', LeafManager::class);
        $this->singleton('commander', Commander::class);

        // tag all commands
        $this->tag(Configure::class, ['command', 'command.configure']);
        $this->tag(Themes::class, ['command', 'command.themes']);
        $this->tag(Generate::class, ['command', 'command.generate']);

        // register the cleanup script
        register_shutdown_function([$this, 'cleanup']);
    }

    /**
     * Clean up after the application run
     */
    public function cleanup()
    {
        if ($this->bound('path.working')) {
            array_map('unlink', glob($this->path('working', '*')));
            rmdir($this['path.working']);
        }
    }

    /**
     * Grab the first item tagged with the given label.
     * It's up to the developer to use a given tag only once in order for this to function as intended.
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
     * @throws Exception
     */
    public function path($type, $path = '')
    {
        $key = 'path.' . @(string) $type;
        if (!$this->bound($key)) {
            throw new Exception('Unknown path type: ' . @(string) $type);
        }
        return $this[$key] . DIRECTORY_SEPARATOR . @(string) $path;
    }

    /**
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
     * @return bool
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
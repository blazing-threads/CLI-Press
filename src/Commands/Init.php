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

namespace BlazingThreads\CliPress\Commands;

use BlazingThreads\CliPress\Commands\Traits\ListsThemes;
use BlazingThreads\CliPress\Managers\ThemeManager;

class Init extends BaseCommand
{
    use ListsThemes;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Interactively create a cli-press.json file for the current directory.');
    }

    /**
     * @inheritdoc
     */
    protected function exec()
    {
        /** @var ThemeManager $themeManager */
        $themeManager = app()->make(ThemeManager::class);
        $defaults = $themeManager->getThemeDefaults('cli-press');

        $this->console->writeln('Answer the following prompts to build a cli-press.json file for the current directory.');

        $this->console->writeln($this->getThemeList());

        $theme = $this->prompt('Theme');

        $defaults = array_merge($defaults, $themeManager->getThemeDefaults('theme'));

        $title = $this->prompt('Title', $defaults['title']);

        $filename = $this->prompt('Filename', $defaults['filename']);

        file_put_contents('cli-press.json', json_encode(compact('theme', 'title', 'filename'), JSON_PRETTY_PRINT));

        $this->console->writeln('<comment>cli-press.json file generated in current directory</comment>');
    }
}

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

class Themes extends BaseCommand
{
    use ListsThemes;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('themes')
            ->setDescription('Show a list of available themes');
    }

    /**
     * @inheritdoc
     */
    protected function exec()
    {
        $list = $this->getThemeList();

        $this->console->writeln($list);
    }
}

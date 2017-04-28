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

use BlazingThreads\CliPress\Managers\PressManager;
use Symfony\Component\Console\Input\InputOption;

class Generate extends BaseCommand
{
    /**
     * @var PressManager
     */
    protected $pressManager;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('generate')
            ->addOption('keep-working', null, InputOption::VALUE_NONE, 'Do not remove the working directory when done')
            ->setDescription('Start up the press and make something beautiful');
    }

    /**
     * @inheritdoc
     */
    protected function exec()
    {
        $this->console->verbose('<comment>Starting the press</comment>');

        $this->pressManager = app()->make(PressManager::class);

        $this->pressManager->setKeepWorkingFiles($this->input->getOption('keep-working'));

        $this->pressManager->generate();

        $this->console->verbose('<comment>Stopping the press</comment>');
    }
}

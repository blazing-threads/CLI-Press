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

use BlazingThreads\CliPress\Traits\InteractsWithDirectories;

class Configure extends BaseCommand
{
    use InteractsWithDirectories;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('configure')
            ->setDescription('Configure this installation of CLI Press');
    }

    /**
     * @inheritdoc
     */
    protected function exec()
    {
        $this->output->writeln('<comment>Configuring CLI Press</comment>');
        $this->showPromptInstructions();

        $configuration = [];
        $configuration['personal.themes'] = $this->getDirectory('Directory for personal themes', app()->path('config', 'themes'), $this->config()->get('personal.themes'));
        $configuration['system.themes'] = $this->getDirectory('Directory for system themes', null, $this->config()->get('system.themes'));
        $this->output->write("<comment>Saving configuration:\n" . json_encode($configuration, JSON_PRETTY_PRINT) . "</comment>\nResult: ");
        $this->config()->setConfiguration($configuration);
        $this->output->writeln($this->config()->saveConfiguration() ? '<info>saved</info>' : '<error>failed</error>');
    }
}

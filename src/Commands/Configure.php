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

class Configure extends BaseCommand
{
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
        $configuration['themes.system'] = $this->prompt('Directory for system themes', null, app()->config()->get('themes.system'));
        $configuration['themes.personal'] = $this->prompt('Directory for personal themes', app()->path('config', 'themes'), app()->config()->get('themes.personal'));
        $this->output->writeln("<comment>Saving configuration:\n" . json_encode($configuration, JSON_PRETTY_PRINT) . "</comment>");

        $confirm = $this->confirm('Continue?');
        $this->output->write('Configuration: ');
        if (!$confirm) {
            $this->output->writeln('<info>aborted</info>');
            return;
        }

        app()->config()->setConfiguration($configuration);

        $this->output->writeln(app()->config()->saveConfiguration() ? '<info>saved</info>' : '<error>failed</error>');

        foreach (['system', 'personal'] as $key) {
            $directory = $configuration["themes.$key"];
            $recursive = true;
            if ($directory && !is_dir($directory) && !mkdir($directory, 0755, $recursive)) {
                $this->output->writeln("<error>Failed creating directory: $directory</error>");
            }
        }
    }
}

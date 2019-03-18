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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class Batch extends BaseCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('batch')
            ->setDescription('Generate a batch of books using a single configuration')
            ->addOption('pattern', null, InputOption::VALUE_OPTIONAL, 'The file pattern used to determine the batch.', '*.md*');
    }

    /**
     * @inheritdoc
     */
    protected function exec()
    {
        /** @var Command $command */
        $command = app()->labeled('command.generate');
        $this->getApplication()->add($command);

        $verbosity = '';
        if ($this->input->hasParameterOption('-v')) {
            $verbosity = '-v';
        } elseif ($this->input->hasParameterOption('-vv')) {
            $verbosity = '-vv';
        } elseif ($this->input->hasParameterOption('-vvv')) {
            $verbosity = '-vvv';
        }

        foreach (glob($this->input->getOption('pattern'), GLOB_BRACE) as $book) {
            $filename = escapeshellarg(json_encode(strstr($book, trim($this->input->getOption('pattern'), '*'), true)));
            $only = escapeshellarg(json_encode([$book]));
            $command = CLI_PRESS . " --ansi generate --filename $filename --only $only --title $filename $verbosity";
            $this->output->writeln("<comment>running $command</comment>");
            passthru($command);
        }
    }
}

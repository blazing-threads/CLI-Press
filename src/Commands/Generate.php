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
     * @var array
     */
    protected $pressOptions;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->pressOptions = json_decode(file_get_contents(app()->path('themes.built-in', 'cli-press/cli-press.json')), true);

        $this
            ->setName('generate')
            ->addOption('keep-working', null, InputOption::VALUE_NONE, 'Do not remove the working directory when done')
            ->setDescription('Start up the press and make something beautiful');

        foreach (array_keys($this->pressOptions) as $option) {
            $this->addOption($option, null, InputOption::VALUE_OPTIONAL, 'Must be valid JSON');
        }
    }

    /**
     * @inheritdoc
     */
    protected function exec()
    {
        $this->console->verbose('<comment>Starting the press</comment>');

        $this->pressManager = app()->make(PressManager::class);

        $this->pressManager->setKeepWorkingFiles($this->input->getOption('keep-working'));

        $cliOptions = [];

        foreach ($this->pressOptions as $option => $default) {
            if ($this->input->getOption($option) !== $default) {
                $cliOption = $this->processPressOption($option, $ignore);
                if (!$ignore) {
                    $cliOptions[$option] = $cliOption;
                }
            }
        }

        $this->pressManager->generate($cliOptions);

        $this->console->verbose('<comment>Stopping the press</comment>');
    }

    /**
     * @param $option
     * @param $ignore
     * @return mixed
     */
    protected function processPressOption($option, &$ignore)
    {
        $ignore = true;
        $value = $this->input->getOption($option);

        if (is_null($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $ignore = false;
        }

        return $decoded;
    }
}

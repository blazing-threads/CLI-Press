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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Themes extends Command
{
    protected $config;

    protected function configure()
    {
        $this
            ->setName('themes')
            ->setDescription('Show a list of available themes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list = "<info>Available Themes:</info>\n";
        foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $theme) {
            $theme = basename($theme);
            if ($theme == 'cli-press') {
                $theme = 'cli-press (base)';
            }
            $list .= "<comment>$theme</comment>\n";
        }
        $output->write($list);
    }
}

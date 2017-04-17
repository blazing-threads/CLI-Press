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

namespace BlazingThreads\CliPress\Traits;

/**
 * Class InteractsWithDirectories
 *
 * @property \Symfony\Component\Console\Output\OutputInterface $output
 *
 * @method prompt($prompt, $default = null, $current = null)
 */
trait InteractsWithDirectories
{

    /**
     * @param $prompt
     * @param null $default
     * @param null $current
     * @return string
     */
    protected function getDirectory($prompt, $default = null, $current = null)
    {
        $directory = false;

        while (true) {
            $directory = $this->prompt($prompt, $default, $current);

            $recursive = true;
            if ($directory && !is_dir($directory) && !mkdir($directory, 0755, $recursive)) {
                $this->output->writeln("<error>Failed to create directory: $directory</error>");
            } else {
                break;
            }
        }

        return $directory;
    }
}
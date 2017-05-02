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

namespace BlazingThreads\CliPress\PressTools;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class PressConsole
{
    /** @var OutputInterface */
    protected $output;

    /**
     * PressConsole constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;

        $style = new OutputFormatterStyle('red');
        $this->output->getFormatter()->setStyle('warn', $style);
    }

    /**
     * @param $message
     */
    public function debug($message)
    {
        if ($this->output->isDebug()) {
            $this->output->writeln($message);
        }
    }

    /**
     * @param $message
     */
    public function verbose($message)
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln($message);
        }
    }

    /**
     * @param $message
     */
    public function veryVerbose($message)
    {
        if ($this->output->isVeryVerbose()) {
            $this->output->writeln($message);
        }
    }

    /**
     * @param $message
     */
    public function write($message)
    {
        $this->output->write($message);
    }

    /**
     * @param $message
     */
    public function writeln($message)
    {
        $this->output->writeln($message);
    }
}
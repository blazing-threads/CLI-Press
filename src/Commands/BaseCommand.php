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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class BaseCommand extends Command
{
    /** @var InputInterface */
    protected $input;

    /** @var QuestionHelper $helper */
    protected $interrogator;

    /** @var OutputInterface */
    protected $output;

    /**
     * @return null|int null or 0 if everything went fine, or an error code
     */
    abstract protected function exec();

    /**
     * @param $question
     * @param bool $default
     * @return bool
     */
    protected function confirm($question, $default = false)
    {
        if (empty($this->interrogator)) {
            $this->interrogator = $this->getHelper('question');
        }

        $question = "\n<question>$question</question> [y/n]\nHit enter for [default: " . (empty($default) ? 'n' : 'y') . "]\n";

        return $this->interrogator->ask($this->input, $this->output, new ConfirmationQuestion($question . 'cli-press> ', $default));
    }

    /**
     * @param $message
     */
    protected function debug($message)
    {
        if ($this->output->isDebug()) {
            $this->output->writeln($message);
        }
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        return $this->exec();
    }

    /**
     * @param string $question
     * @param null|string $default
     * @param null|string $current
     * @return mixed
     */
    protected function prompt($question, $default = null, $current = null)
    {
        if (empty($this->interrogator)) {
            $this->interrogator = $this->getHelper('question');
        }

        $prompt = "\n<question>" . @(string) $question . "</question>\n";

        if (empty($current)) {
            $prompt .= 'Hit enter for ';
        }

        // add default to prompt
        if (!empty($default)) {
            $default = @(string) $default;
            $prompt .= "<info>[default: $default]</info>\n";
        } else {
            $prompt .= "<info>[default: @null]</info>\n";
        }

        // add current to prompt
        if (!empty($current)) {
            $current = @(string) $current;
            $prompt .="Hit enter for <comment>(current: $current)</comment>\n";
        }

        $answer = $this->interrogator->ask($this->input, $this->output, new Question($prompt . 'cli-press> '));

        if ($answer === '@null') {
            return null;
        }

        if ($answer === '@default') {
            return $default;
        }

        if (empty($answer)) {
            return !empty($current) ? $current : $default;
        }

        return $answer;
    }

    protected function showPromptInstructions()
    {
        $this->output->writeln('For each prompt, simply hitting enter will keep the current value if it is set or will use the default value if there is one.');
        $this->output->writeln('You may also type @null for an empty response, or @default to use the default value.');
    }

    /**
     * @param $message
     */
    protected function verbose($message)
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln($message);
        }
    }

    /**
     * @param $message
     */
    protected function veryVerbose($message)
    {
        if ($this->output->isVeryVerbose()) {
            $this->output->writeln($message);
        }
    }
}
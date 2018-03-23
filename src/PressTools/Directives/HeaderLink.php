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

namespace BlazingThreads\CliPress\PressTools\Directives;

use BlazingThreads\CliPress\PressTools\PressConsole;
use BlazingThreads\CliPress\PressTools\PressInstructionStack;

class HeaderLink extends BaseDirective
{
    /**
     * @var int
     */
    protected $currentSection = 0;

    /**
     * @var int
     */
    protected $currentSubSection = 0;

    /**
     * @var int
     */
    protected $currentSubSubSection = 0;

    /**
     * @var PressInstructionStack $instructions
     */
    protected $instructions;

    /**
     * @var int
     */
    protected $lastSectionType = 0;

    /**
     * @var array
     */
    protected $links = [];

    /**
     * @var string
     */
    protected $pattern = '/^(@|)(#{1,6})(.+)$/m';

    public function __construct(PressInstructionStack $instructions)
    {
        $this->instructions = $instructions;
    }

    /**
     * @param $matches
     * @return string
     */
    protected function escape($matches)
    {
        $markup = new SyntaxHighlighter();
        return $markup->addLiteral('#')
            ->addOption('{1,6}')
            ->addPlainText('Header text');
    }

    /**
     * @param $matches
     * @return string
     */
    protected function process($matches)
    {
        $name = strtolower(preg_replace('/\W/', '-', trim($matches[3])));

        if (in_array($name, $this->links)) {
            do {
                $number = 1;
            } while (in_array("$name-$number", $this->links) && $number++);

            $name .= "-$number";
            $this->warn("Warning duplicate header link detected for header '{$matches[0]}'. Appending '-$number' to link name: $name.");
        }

        $this->links[] = $name;

        $sectionNumber = '';

        if ($this->instructions->sectionNumbering && ($hTag = strlen($matches[2])) < 4) {
            $sectionNumber = $this->setSectionNumber($hTag);
        }

        return "{$matches[2]} <a class=\"header-link\" href=\"#$name\" name=\"$name\">$sectionNumber{$matches[3]}</a>";
    }

    /**
     * @param $hTag
     * @return string
     */
    protected function setSectionNumber($hTag)
    {
        switch ($this->lastSectionType) {
            case 0:
                if ($hTag != 1) {
                    $this->warn("First <H> tag is <H$hTag> and not <H1>");
                }
                $this->currentSection++;
                break;
            case 1:
                if ($hTag == 1) {
                    $this->currentSection++;
                    $this->currentSubSection = 0;
                    $this->currentSubSubSection = 0;
                } elseif ($hTag == 2) {
                    $this->currentSubSection++;
                    $this->currentSubSubSection = 0;
                } else {
                    $this->warn('Expected <H2> but got <H3>');
                    $this->currentSubSubSection++;
                }
                break;
            case 2:
                if ($hTag == 1) {
                    $this->currentSection++;
                    $this->currentSubSection = 0;
                    $this->currentSubSubSection = 0;
                } elseif ($hTag == 2) {
                    $this->currentSubSection++;
                    $this->currentSubSubSection = 0;
                } else {
                    $this->currentSubSubSection++;
                }
                break;
            case 3:
                if ($hTag == 1) {
                    $this->currentSection++;
                    $this->currentSubSection = 0;
                    $this->currentSubSubSection = 0;
                } elseif ($hTag == 2) {
                    $this->currentSubSection++;
                    $this->currentSubSubSection = 0;
                } else {
                    $this->currentSubSubSection++;
                }
        }

        $this->lastSectionType = $hTag;

        switch ($hTag) {
            case 1:
                return "$this->currentSection. ";
            case 2:
                return "$this->currentSection.$this->currentSubSection. ";
            case 3:
                return "$this->currentSection.$this->currentSubSection.$this->currentSubSubSection. ";
        }

        return '';
    }

    /**
     * @param $warning
     */
    protected function warn($warning)
    {
        app()->make(PressConsole::class)->writeLn("<warn>$warning</warn>");
    }
}
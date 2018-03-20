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

class HeaderLink extends BaseDirective
{

    /**
     * @var array
     */
    protected $links = [];

    /**
     * @var string
     */
    protected $pattern = '/^(@|)(#{1,6})(.+)$/m';

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
            app()->make(PressConsole::class)->writeLn("Warning duplicate header link detected for header '{$matches[0]}'. Appending '-$number' to link name: $name.");
        }
        $this->links[] = $name;
        return "{$matches[2]} <a class=\"header-link\" href=\"#$name\" name=\"$name\">{$matches[3]}</a>";
    }
}
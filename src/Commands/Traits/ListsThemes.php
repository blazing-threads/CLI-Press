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

namespace BlazingThreads\CliPress\Commands\Traits;

trait ListsThemes
{

    /**
     * @return string
     */
    protected function getThemeList()
    {
        $list = "Available Themes\n";

        foreach (['personal', 'system'] as $collection) {
            if ($dir = app()->config()->get("themes.$collection")) {
                $list .= "<info>$collection:</info>\n";
                foreach (glob($dir . '/*', GLOB_ONLYDIR) as $theme) {
                    $list .= '<comment>' . basename($theme) . "</comment>\n";
                }
                $list .= "\n";
            }
        }

        $list .= "<info>built-in:</info>\n";

        foreach (glob(app()->path('themes.built-in', '*'), GLOB_ONLYDIR) as $theme) {
            $theme = basename($theme);
            if ($theme == 'cli-press') {
                $theme = 'cli-press (base)';
            }
            $list .= "<comment>$theme</comment>";
        }

        return $list;
    }

}
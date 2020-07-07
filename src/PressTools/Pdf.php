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

use BlazingThreads\CliPress\CliPressException;
use mikehaertl\wkhtmlto\Pdf as BasePdf;

class Pdf extends BasePdf
{
    protected static $enableLocalFileAccess;

    public function __construct($options = null)
    {
        parent::__construct($options);

        if (is_null(self::$enableLocalFileAccess)) {
            /** @var PressInstructionStack $instructions */
            $instructions = app()->make(PressInstructionStack::class);
            if (!$instructions->enableLocalFileAccess) {
                self::$enableLocalFileAccess = false;
            } else {
                try {
                    $test = new BasePdf(['enable-local-file-access']);
                    $test->addPage('', null, BasePdf::TYPE_HTML);
                    $result = $test->saveAs($name = tempnam(sys_get_temp_dir(), rand(100, 999)));
                    @unlink($name);
                    if ($result) {
                        self::$enableLocalFileAccess = true;
                    } else {
                        $this->reportLocalFileAccessFailure($test->getError());
                    }
                } catch (\Exception $e) {
                    $this->reportLocalFileAccessFailure($e->getMessage());
                }
            }
        }

        if (self::$enableLocalFileAccess) {
            $this->setOptions(['enable-local-file-access']);
        }

        $this->setOptions(['cache-dir' => $this->getCacheDir(), 'page-size' => app()->make(PressInstructionStack::class)->pageSize]);
    }

    /**
     * @return string
     * @throws CliPressException
     */
    protected function getCacheDir()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cli-press-web-cache';
        if (!file_exists($dir) && !mkdir($dir)) {
            throw new CliPressException("Failed to make cache dir: $dir.");
        }

        return $dir;
    }

    /**
     * @param $error
     * @throws CliPressException
     */
    protected function reportLocalFileAccessFailure($error)
    {
        /** @var PressConsole $console */
        $console = app()->make(PressConsole::class);
        $console->writeln("<error>Failed to enable local file access for wkhtmltopdf.  Does your installed version have this option (--enable-local-file-access)?  If not you can turn it off in your cli-press.json file by setting 'enable-local-file-access' to 'false'.</error>");
        throw new CliPressException($error);
    }
}
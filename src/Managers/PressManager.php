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

namespace BlazingThreads\CliPress\Managers;

use BlazingThreads\CliPress\CliPressException;
use BlazingThreads\CliPress\Pdf;
use BlazingThreads\CliPress\PressConsole;
use BlazingThreads\CliPress\PressdownParser;
use BlazingThreads\CliPress\PressInstructionStack;

class PressManager
{
    /**
     * @var PressConsole
     */
    protected $console;

    /**
     * @var int
     */
    protected $currentPageOffset = 0;

    /**
     * List of to-be-PDF'ed documents
     * @var array
     */
    protected $documentList = [];

    /**
     * List of path patterns to ignore
     * @var array
     */
    protected $ignored;

    /**
     * Current press directory configuration
     * @var PressInstructionStack
     */
    protected $instructions;

    /**
     * Last page number processed
     * @var int
     */
    protected $lastPage;

    /**
     * @var LeafManager
     */
    protected $leafManager;

    /**
     * @var bool
     */
    protected $keepWorkingFiles;

    /**
     * @var PressdownParser
     */
    protected $pressDown;

    /**
     * Current press directory
     * @var string
     */
    protected $pressDirectory;

    /**
     * Path to root press directory
     * @var string
     */
    protected $pressRootPath;

    /**
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * Current working directory
     * @var string
     */
    protected $workingDirectory;

    /**
     * Path to root working directory
     * @var string
     */
    protected $workingRootPath;

    public function __construct(TemplateManager $templateManager, ThemeManager $themeManager, LeafManager $leafManager, PressInstructionStack $instructions, PressConsole $console)
    {
        $this->templateManager = $templateManager;
        $this->themeManager = $themeManager;
        $this->leafManager = $leafManager;
        $this->instructions = $instructions;
        $this->console = $console;

        $this->pressDown = new PressdownParser();
        $this->pressRootPath = app()->path('press-root');
    }

    /**
     *
     */
    public function generate()
    {
        $this->setWorkingRootPath($this->keepWorkingFiles);
        $this->setWorkingDirectory();

        // in case these files are still hanging around from a previous press run, we need to remove them
        array_map('unlink', glob($this->workingRootPath . DIRECTORY_SEPARATOR . '*.xml'));

        $this->generateRecursively($this->pressRootPath);

        $this->processDocumentList();

        $this->setWorkingDirectory($this->workingRootPath);

        if (!chdir($this->pressRootPath)) {
            throw new CliPressException('Failed to change press directory to: ' . $this->pressRootPath);
        }

        if ($this->instructions->bookToc) {
            $this->addTableOfContents();
        }

        if ($this->instructions->hasRootCover) {
            $pdf = new Pdf([
                'footer-html' => $this->getWorkingFilePath('cli-press_cover-footer.html'),
                'header-html' => $this->getWorkingFilePath('cli-press_cover-header.html'),
            ]);

            $pdf->addPage($this->getWorkingFilePath('cli-press_cover.html'));

            if (!$pdf->saveAs($this->getWorkingFilePath('cli-press_cover.pdf'))) {
                throw new CliPressException("Error processing cover file: " . $pdf->getError());
            }

            if ($this->instructions->chapterStartsRight) {
                $prepend = true;
                $this->addBlankPage($prepend);
            }

            $this->prependFile($this->getWorkingFilePath('cli-press_cover.pdf'));
        }

        if (!$this->leafManager->merge($this->instructions->filename . '.pdf')) {
            $this->console->verbose("<error>Could not save to {$this->instructions->filename}.pdf</error>");
        } else {
            $this->console->verbose("<comment>Saved to {$this->instructions->filename}.pdf</comment>");
        }
    }

    /**
     * @param $boolean
     */
    public function setKeepWorkingFiles($boolean)
    {
        $this->keepWorkingFiles = @(bool) $boolean;
    }

    /**
     * @param bool $prepend
     * @throws CliPressException
     */
    protected function addBlankPage($prepend = false)
    {
        $blankPage = $this->workingRootPath . DIRECTORY_SEPARATOR . 'cli-press_blank.pdf';

        if (!file_exists($blankPage)) {
            $pdf = new Pdf();

            $pdf->addPage('<html><body></body></html>');

            if (!$pdf->saveAs($blankPage)) {
                throw new CliPressException("Error processing blank page: " . $pdf->getError());
            }
        }

        $this->addFile($blankPage, $prepend);
    }

    /**
     * @param $file
     * @param bool $prepend
     */
    protected function addFile($file, $prepend = false)
    {
        $this->console->veryVerbose(($prepend ? 'prepending ' : 'adding ') . $file);

        $this->leafManager->addFile($file, $prepend);
    }

    /**
     * @throws CliPressException
     */
    protected function addTableOfContents()
    {
        $outlines = glob($this->workingRootPath . DIRECTORY_SEPARATOR . '*.xml');

        $xml = new \DOMDocument();
        $xml->load(array_shift($outlines));
        foreach ($xml->firstChild->childNodes as $node) {
            $node = $xml->importNode($node, true);
            if ($node->hasAttributes()) {
                $xml->firstChild->appendChild($node);
            }
        }

        foreach ($outlines as $outline) {
            $chapter = new \DOMDocument();
            $chapter->load($outline);
            foreach ($chapter->firstChild->childNodes as $node) {
                $node = $xml->importNode($node, true);
                if ($node->hasAttributes()) {
                    $xml->firstChild->appendChild($node);
                }
            }
            unset($chapter);
        }

        $xsl = new \DOMDocument();
        $xsl->loadXML($this->themeManager->getFirstFile('toc.xsl.twig'));

        $processor = new \XSLTProcessor();
        $processor->importStylesheet($xsl);

        $this->saveWorkingFile('cli-press_toc.html', $processor->transformToXml($xml));

        $pdf = new Pdf();

        $pdf->addPage($this->getWorkingFilePath('cli-press_toc.html'));

        if (!$pdf->saveAs($this->getWorkingFilePath('cli-press_toc.pdf'))) {
            throw new CliPressException("Error processing table of contents: " . $pdf->getError());
        }

        $this->prependFile($this->getWorkingFilePath('cli-press_toc.pdf'));
    }

    protected function closeDocumentSection($section)
    {
        $sectionPath = $this->getWorkingFilePath("cli-press_$section");
        $parts = ['', '-header', '-footer'];
        foreach ($parts as $part) {
            if (! $markup = file_get_contents($sectionPath . $part . '.html')) {
                throw new CliPressException("Failed to open document section for closure: $sectionPath$part.html)");
            }

            if (!file_put_contents($sectionPath . $part . '.html', $this->pressDown->close($markup))) {
                throw new CliPressException("Failed to close document section: $sectionPath$part.html");
            }
        }
    }

    /**
     * @param string $pressDirectory
     * @throws CliPressException
     */
    protected function generateRecursively($pressDirectory)
    {
        if (!chdir($pressDirectory)) {
            throw new CliPressException('Failed to change press directory to: ' . $pressDirectory);
        }

        $this->console->veryVerbose("entering $pressDirectory");

        $this->pressDirectory = $pressDirectory;
        $this->setConfiguration();
        $this->setIgnored();

        $pressFiles = glob('*.{' . implode(',', $this->instructions->extensions) . '}', GLOB_BRACE);

        if(!empty($pressFiles)) {

            $this->console->debug("generating leaf $pressDirectory");

            $this->setWorkingDirectory();

            $this->documentList[] = ['chapter-starting', $this->instructions->chapterStartsRight, $this->instructions->chapterStartsAfterEmptyPage];

            $coverPageHtml = $this->generateCoverPageHtml();

            if ($coverPageHtml) {
                $this->saveWorkingFile('cli-press_cover.html', $coverPageHtml);
                $this->saveWorkingFile('cli-press_cover-footer.html', $this->generateHtml($this->getCoverType() . '-cover-footer'));
                $this->saveWorkingFile('cli-press_cover-header.html', $this->generateHtml($this->getCoverType() . '-cover-header'));

                $this->instructions->hasCover = true;

                if (!$this->isRootDocument()) {
                    $this->documentList[] = ['cover', $this->workingDirectory, $this->getCoverType()];
                }
            }

            $this->documentList[] = ['body', $this->workingDirectory, $this->instructions->filename, $pressDirectory];

            $this->saveWorkingFile('cli-press_body-header.html', $this->generateHtml('header'));
            $this->saveWorkingFile('cli-press_body.html', $this->generateBodyHtml($pressFiles));
            $this->saveWorkingFile('cli-press_body-footer.html', $this->generateHtml('footer'));

            $this->console->verbose("<info>prepared leaf $pressDirectory</info>");
        }

        $chapters = glob($pressDirectory . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        foreach ($chapters as $directory) {
            if ($this->isIgnored($directory)) {
                $this->console->veryVerbose("ignoring $directory");
                continue;
            }

            $this->generateRecursively($directory);
        }

        if (!empty($chapters)) {
            if (!chdir($pressDirectory)) {
                throw new CliPressException('Failed to change press directory to: ' . $pressDirectory);
            }

            $this->pressDirectory = $pressDirectory;
            if (!$this->isRootDocument()) {
                $this->instructions->pop();
                $this->setIgnored();
            }
            $this->setWorkingDirectory();
        }

        $this->console->debug('finished in ' . $pressDirectory);
    }

    /**
     * Generates an HTML file for all of the files provided
     * @param $files
     * @return string
     */
    protected function generateBodyHtml($files) {
        $__baseCss = $this->themeManager->getThemeFile('body.css.twig', [], true);
        $__themeCss = $this->themeManager->getThemeFile('body.css.twig');
        $__commonJs = $this->themeManager->getFirstFile('common.js');
        $__html = '';
        while ($file = array_shift($files)) {
            if (is_dir($file) || $this->isIgnored($file)) {
                $this->console->debug((is_file($file) ? 'skipping directory ' : 'ignoring ') . $file);
                continue;
            }

            if (!empty($this->instructions->hasCover) && $file == 'cover.md') {
                continue;
            }

            // insert a page break between files if configured
            if (!empty($__html) && $this->instructions->autoPageBreak) {
                $__html .= "\n{break}\n";
            }

            $__html .= $this->templateManager->renderString(file_get_contents($file), $this->instructions->constants);
        }
        $__html = $this->pressDown->parse($__html);
        $__withFA = $__html->hasFA();
        return $this->themeManager->getFirstFile('body-layout.html.twig', compact('__baseCss', '__themeCss', '__commonJs', '__html', '__withFA'));
    }

    /**
     * @param $type
     * @param array $variables
     * @return string
     */
    protected function generateHtml($type, $variables = [])
    {
        $__baseCss = $this->themeManager->getThemeFile($type . '.css.twig', [], true);
        $__themeCss = $this->themeManager->getThemeFile($type . '.css.twig');
        $__commonJs = $this->themeManager->getFirstFile('common.js');
        $__content = $this->pressDown->parse($this->themeManager->getFirstFile($type . '.html.twig', $variables));
        $__withFA = $__content->hasFA();
        return $this->themeManager->getFirstFile($type . '-layout.html.twig', compact('__baseCss', '__themeCss', '__commonJs', '__content', '__withFA'));
    }

    /**
     * Generates an HTML cover page from a cover.md file
     * @return string
     */
    protected function generateCoverPageHtml() {
        if (!file_exists('cover.md')) {
            if (($this->isRootDocument() && !$this->instructions->simpleRootCover) || (!$this->isRootDocument() && !$this->instructions->useChapterCovers)) {
                return '';
            }
            $__content = $this->isRootDocument() ? "<h1>{$this->instructions->title}</h1>" : "<h1>{$this->instructions->chapterTitle}</h1>";
        } else {
            $__content = file_get_contents('cover.md');
        }
        return $this->generateHtml($this->getCoverType() . '-cover', compact('__content'));
    }

    /**
     * Returns the cover type based on the current context and configuration
     * @return string
     */
    protected function getCoverType()
    {
        return empty($this->instructions->coverType) || $this->instructions->coverType == 'default'
            ? ($this->isRootDocument() ? 'root' : 'chapter')
            : $this->instructions->coverType;
    }

    /**
     * @param $file
     * @return string
     */
    protected function getWorkingFilePath($file)
    {
        return $this->workingDirectory . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @return bool
     */
    protected function isChapter()
    {
        return $this->pressDirectory !== $this->pressRootPath;
    }

    /**
     * @param $path
     * @return bool
     */
    protected function isIgnored($path)
    {
        $path = basename($path);
        foreach ($this->ignored as $ignore) {
            if (preg_match('/^' . $ignore . '$/', $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isRootDocument()
    {
        return $this->pressRootPath === $this->pressDirectory;
    }

    /**
     * @param $file
     */
    protected function prependFile($file)
    {
        $this->addFile($file, true);
    }

    /**
     *
     */
    protected function processDocumentList()
    {
        foreach ($this->documentList as $documentInfo) {

            switch ($documentInfo[0]) {
                case 'chapter-starting':
                    // if starts after empty page, ensure it will
                    if ($documentInfo[2]) {
                        $offset = $this->currentPageOffset % 2 == 1 ? 1 : 2;
                        $this->currentPageOffset += $offset;
                        for ($blank = 0; $blank < $offset; $blank++) {
                            $this->addBlankPage();
                        }
                    }
                    // if starts on the right side, ensure it will
                    if ($documentInfo[1] && $this->currentPageOffset % 2 == 1) {
                        $this->currentPageOffset++;
                        $this->addBlankPage();
                    }
                    break;

                case 'cover':
                    $this->setWorkingDirectory($documentInfo[1]);

                    $this->closeDocumentSection('cover');

                    $pdf = new Pdf([
                        'page-offset' => $this->currentPageOffset,
                        'footer-html' => $this->getWorkingFilePath('cli-press_cover-footer.html'),
                        'header-html' => $this->getWorkingFilePath('cli-press_cover-header.html'),
                    ]);

                    $pdf->addPage($this->getWorkingFilePath('cli-press_cover.html'));

                    if (!$pdf->saveAs($this->getWorkingFilePath('cli-press_cover.pdf'))) {
                        throw new CliPressException("Error processing cover file: " . $pdf->getError());
                    }

                    $this->addFile($this->getWorkingFilePath('cli-press_cover.pdf'));

                    $this->currentPageOffset += $this->leafManager->getPageCount($this->getWorkingFilePath('cli-press_cover.pdf'));
                    break;

                case 'body':
                    $this->setWorkingDirectory($documentInfo[1]);

                    $this->closeDocumentSection('body');

                    $pdf = new Pdf([
                        'dump-outline' => $this->workingRootPath . DIRECTORY_SEPARATOR . sprintf('%06d', $this->currentPageOffset) . '_outline.xml',
                        'page-offset' => $this->currentPageOffset,
                        'header-html' => $this->getWorkingFilePath('cli-press_body-header.html'),
                        'footer-html' => $this->getWorkingFilePath('cli-press_body-footer.html'),
                    ]);

                    $pdf->addPage($this->getWorkingFilePath('cli-press_body.html'));

                    if (!$pdf->saveAs($this->getWorkingFilePath($documentInfo[2] . '.pdf'))) {
                        throw new CliPressException("Error processing leaf $documentInfo[3]: " . $pdf->getError());
                    }

                    $this->addFile($this->getWorkingFilePath($documentInfo[2] . '.pdf'));

                    $this->setPageOffset($this->getWorkingFilePath($documentInfo[2] . '.pdf'));

                    $this->console->debug('added file: ' . $this->getWorkingFilePath($documentInfo[2] . '.pdf'));

                    $this->console->verbose('<info>processed leaf ' . $this->pressRootPath . str_replace($this->workingRootPath, '', $documentInfo[1]) . '</info>');
                    break;
            }
        }
    }

    /**
     * Sets the configuration for the current press directory.
     * If there is already an active configuration, it gets pushed onto the stack.
     */
    protected function setConfiguration()
    {
        // push the current configuration onto the stack
        if (file_exists('cli-press.json')) {
            $json = json_decode(file_get_contents('cli-press.json'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->instructions->stack($json, $this->pressDirectory);
            }
        }

        if ($this->isRootDocument()) {
            if ($this->instructions->simpleRootCover || file_exists('cover.md')) {
                $this->instructions->hasRootCover = true;
                // add one for the cover
                $this->currentPageOffset++;
            }

            if ($this->instructions->bookToc) {
                // add one for the ToC, two if we need to start on the right side (but only if there's a cover)
                $this->currentPageOffset +=  !$this->instructions->chapterStartsRight || !$this->instructions->hasRootCover ? 1 : 2;
            }
        }

        $this->console->veryVerbose(['Configuration for ' . $this->pressDirectory, $this->instructions]);
    }

    /**
     *
     */
    protected function setIgnored()
    {
        $this->ignored = $this->instructions->getIgnored();

        $this->console->debug(['Ignoring:', json_encode($this->ignored, JSON_PRETTY_PRINT)]);
    }

    protected function setPageOffset($file)
    {
        $this->currentPageOffset += $this->leafManager->getPageCount($file);
    }

    /**
     * Save a working file into the current working directory
     * @param $file
     * @param $contents
     */
    protected function saveWorkingFile($file, $contents)
    {
        file_put_contents($this->workingDirectory . DIRECTORY_SEPARATOR . $file, $contents);
    }

    /**
     * @param null $forceDirectory
     * @throws CliPressException
     */
    protected function setWorkingDirectory($forceDirectory = null)
    {
        if ($forceDirectory && file_exists($forceDirectory)) {
            $this->workingDirectory = $forceDirectory;
            return;
        }
        $this->workingDirectory = $this->workingRootPath . str_replace($this->pressRootPath, '', $this->pressDirectory);
        if (!is_dir($this->workingDirectory) && !mkdir($this->workingDirectory, 0755, true)) {
            throw new CliPressException('Failed to create working directory: ' . $this->workingDirectory);
        }

        $this->console->debug("set working directory: $this->workingDirectory");
    }

    /**
     * Set the root path for the working directory
     * @param bool $keepWorkingFiles
     * @throws CliPressException
     */
    protected function setWorkingRootPath($keepWorkingFiles)
    {
        $this->workingRootPath = sys_get_temp_dir() . '/cli-press_' . md5(app()['path.project']);
        if (!file_exists($this->workingRootPath)) {
            @mkdir($this->workingRootPath);
        }

        if (!is_dir($this->workingRootPath)) {
            throw new CliPressException('Failed to create working path: ' . $this->workingRootPath);
        }

        if (!$keepWorkingFiles) {
            // bind this to the application which will take care of removing it during shutdown
            app()['path.working'] = $this->workingRootPath;
        }
    }

}
<?php
//@todo нужен ли?
namespace tfl\observers;

use tfl\utils\tFile;
use tfl\utils\tObfuscator;

trait ResourceObserver
{
    protected function cleanWebFolder(): void
    {
        foreach ($this->cssFiles as $cssFileName) {
            tFile::removeIfExists(zROOT . 'web/css/' . $cssFileName . '.css');
        }
        foreach (array_keys($this->jsFiles) as $jsFileName) {
            tFile::removeIfExists(zROOT . 'web/js/' . $jsFileName . '.js');
        }
    }

    private function checkFoldersExists()
    {
        tFile::checkDirExists('web/css/fonts/');
        tFile::checkDirExists('web/js/');
    }

    protected function setWebFolder(): void
    {
        $this->checkFoldersExists();
        $this->copyCssFiles();
        $this->copyFontsFiles();
        $this->sliceJsFiles();
    }

    private function copyCssFiles()
    {
        foreach ($this->cssFiles as $cssFileName) {
            $file = zROOT . 'resource/assets/css/' . $cssFileName . '.css';
            $cssFile = zROOT . 'web/css/' . $cssFileName . '.css';
            if (tFile::file_exists($cssFile)) {
                continue;
            }

            if (tFile::file_exists($file)) {
                ob_start();
                require_once $file;
                $cssData = ob_get_clean();

                tFile::file_put_contents($cssFile, tObfuscator::compressCSS($cssData));
            }
        }
    }

    private function copyFontsFiles()
    {
        foreach ($this->fontsFiles as $filename) {
            tFile::copyFile(zROOT . 'resource/assets/fonts/' . $filename, zROOT . 'web/css/fonts/' . $filename);
        }
    }

    private function sliceJsFiles()
    {
        foreach ($this->jsFiles as $groupName => $group) {
            $jsFile = zROOT . 'web/js/' . $groupName . '.js';

            if (tFile::file_exists($jsFile)) {
                continue;
            }

            ob_start();
            foreach ($group as $index => $fileName) {
                $file = zROOT . 'resource/assets/js/' . $fileName . '.js';
                if (tFile::file_exists($file)) {
                    require_once $file;
                    echo PAGE_EOL;
                }
            }
            $jsData = ob_get_clean();

            tFile::file_put_contents($jsFile, tObfuscator::compressJS($jsData));
        }
    }

    protected function getReplacedConstantsLang(): string
    {
        return 'en';
    }

    protected function getReplacedConstantsMetatags(): string
    {
        $metaValues = [];
        $t = $this->seedMetatagsData($metaValues);

        return implode(PAGE_EOL, $t) . PAGE_EOL;
    }

    protected function getReplacedConstantsCss(string $type = self::TYPE_HEADER): string
    {
        $t = '';
        if ($type == self::TYPE_HEADER) {
            foreach ($this->cssFiles as $fileName) {
                $file = ROOT . 'css/' . $fileName . '.css';
                $filemtime = tFile::filemtime(zROOT . 'web/css/' . $fileName . '.css');

                $file = $file . '?t=' . $filemtime;

                $t .= '<link href="' . $file . '" rel="stylesheet" type="text/css" media="screen"/>';
                $t .= PAGE_EOL;
            }
        }

        return $t;
    }

    protected function getReplacedConstantsJs(string $type = self::TYPE_HEADER): string
    {
        $t = '';
        if ($type == self::TYPE_HEADER) {
            foreach (array_keys($this->jsFiles) as $fileName) {
                $file = ROOT . 'js/' . $fileName . '.js';
                $filemtime = tFile::filemtime(zROOT . 'web/js/' . $fileName . '.js');

                $file = $file . '?t=' . $filemtime;

                $t .= '<script type="text/javascript" src="' . $file . '"></script>';
                $t .= PAGE_EOL;
            }
        }

        return $t;
    }

    private function seedMetatagsData(array $metaValues = [])
    {
        $metaValues['imageurl'] = $metaValues['imageurl'] ?? null;

        $metatags = [];
        $metatags[] = '<title>TFL</title>';

        $meta = [
            [
                'name' => 'viewport',
                'content' => $metaValues['viewport'] ?? '',
            ],
            [
                'name' => 'description',
                'content' => $metaValues['description'] ?? '',
            ],
            [
                'http-equiv' => 'X-UA-Compatible',
                'content' => 'IE=EDGE',
            ],
            [
                'http-equiv' => 'Content-Type',
                'content' => 'text/html; charset=utf-8',
            ],
            [
                'http-equiv' => 'Cache-Control',
                'content' => 'must-revalidate',
            ],
            [
                'name' => 'keywords',
                'content' => $metaValues['keywords'] ?? '',
            ],
            [
                'name' => 'author',
                'content' => $metaValues['author'] ?? '',
            ],
            [
                'name' => 'copyright',
                'content' => $metaValues['copyright'] ?? '',
            ],
            [
                'name' => 'Robots',
                'content' => 'ALL',
            ],
            [
                'name' => 'document-state',
                'content' => 'dynamic',
            ],
            [
                'name' => 'revisit',
                'content' => '5 minutes',
            ],
            [
                'name' => 'revisit-after',
                'content' => '5 minutes',
            ],

            [
                'name' => 'og:title',
                'content' => $metaValues['title'] ?? '',
            ],
            [
                'name' => 'og:site_name',
                'content' => $metaValues['title'] ?? '',
            ],
            [
                'name' => 'og:description',
                'content' => $metaValues['description'] ?? '',
            ],
            [
                'name' => 'og:image',
                'content' => $metaValues['imageurl'],
            ],
            [
                'name' => 'og:url',
                'content' => ROOT,
            ],
        ];

        $adminView = false;
        $hideInAdmin = [
            'keywords',
            'Robots',
            'og:title',
            'og:site_name',
            'og:description',
            'og:image',
            'og:url',
        ];

        foreach ($meta as $index => $oneMeta) {
            if ($adminView && in_array($index, $hideInAdmin)) {
                continue;
            }

            $metatag = '<meta';
            foreach ($oneMeta as $nameIndex => $nameValue) {
                $metatag .= ' ' . $nameIndex . '="' . $nameValue . '"';
            }
            $metatag .= '/>';

            $metatags[] = $metatag;
        }

        $metatags[] = '<link rel="image_src" href="' . $metaValues['imageurl'] . '">';

        return $metatags;
    }
}
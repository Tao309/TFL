<?php

namespace tfl\builders;

use tfl\observers\{
    ResourceObserver,
    SectionObserver
};
use tfl\utils\tFile;

class SectionBuilder
{
    use SectionObserver, ResourceObserver;

    const DEFAULT_TEMPLATE = 'default';
    const TYPE_HEADER = 'header';
    const TYPE_BODY = 'body';
    const TYPE_FOOTER = 'footer';

    private $route;
    private $routeType;
    private $content;

    /**
     * @var array
     */
    private $cssFiles;
    /**
     * @var array
     */
    private $jsFiles;
    /**
     * @var array
     */
    private $fontsFiles;
    /**
     * Переменные для замены как текст в html
     * @var array
     */
    private $assignVars = [];
    /**
     * вычисляемые переменные для замены в html
     * @var array
     */
    private $computeVars = [];

    public function __construct($route, $routeType)
    {
        $this->route = $route;
        $this->routeType = $routeType;

        $this->cssFiles = \TFL::source()->config('css');
        $this->jsFiles = \TFL::source()->config('js');
        $this->fontsFiles = \TFL::source()->config('fonts');

//        $this->cleanWebFolder();
        $this->setWebFolder();
    }

    private function getTemplateName()
    {
        return self::DEFAULT_TEMPLATE;
    }

    private function getPath()
    {
        return zROOT . 'resource/templates/' . $this->getTemplateName() . '/';
    }

    public function renderSection()
    {
        $content = $this->renderHeader() . $this->renderBody() . $this->renderFooter();
//        $content = tObfuscator::compress_code($content);

        return $content;
    }

    public function addAssignVars(array $vars = [])
    {
        $this->assignVars = $vars;
    }

    public function getAssignVars()
    {
        return $this->assignVars;
    }

    public function addComputeVars(array $vars = [])
    {
        $this->computeVars = $vars;
    }

    public function getComputeVars()
    {
        return $this->computeVars;
    }

    private function getContent(string $name, string $type)
    {
        $file = $this->getPath() . $name . '.html';

        if (!tFile::file_exists($file)) {
            return "<pre>Template file $name not found</pre>";
        }

        ob_start();
        require_once $file;
        $this->content = ob_get_clean();

        return $this->replaceConstants($type);
    }

    private function renderHeader()
    {
        return $this->getContent('sectionHeader', self::TYPE_HEADER);
    }

    private function renderBody()
    {
        return $this->getContent($this->route . '/' . $this->routeType, self::TYPE_BODY);
    }

    private function renderFooter()
    {
        return $this->getContent('sectionFooter', self::TYPE_FOOTER);
    }

}
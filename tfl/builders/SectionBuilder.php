<?php

namespace tfl\builders;

use tfl\observers\SectionObserver;

class SectionBuilder
{
    use SectionObserver;

    const DEFAULT_TEMPLATE = 'default';
    const TYPE_HEADER = 'header';
    const TYPE_BODY = 'body';
    const TYPE_FOOTER = 'footer';

    private $route;
    private $routeType;
    private $content;

    public function __construct($route, $routeType)
    {
        $this->route = $route;
        $this->routeType = $routeType;
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
        return $this->renderHeader() . $this->renderBody() . $this->renderFooter();
    }

    private function getContent(string $name, string $type)
    {
        $file = $this->getPath() . $name . '.html';

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
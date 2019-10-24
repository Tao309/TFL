<?php

namespace tfl\builders;

use tfl\observers\{
    ResourceObserver,
    SectionObserver
};
use tfl\interfaces\InitControllerBuilderInterface;
use tfl\utils\tFile;

class SectionBuilder
{
    use SectionObserver, ResourceObserver;

    const DEFAULT_TEMPLATE = 'default';
    const TYPE_HEADER = 'header';
    const TYPE_BODY = 'body';
    const TYPE_FOOTER = 'footer';

    private $routeDirection;
    private $route;
    private $routeType;
    private $content;

    /**
     * @var ControllerBuilder
     */
    private $contBuilder;
    /**
     * @var InitControllerBuilderInterface
     */
    private $initBuilder;

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

    public function __construct(
        ControllerBuilder $contBuilder,
        InitControllerBuilderInterface $initBuilder
    )
    {
        $this->setContBuilder($contBuilder);
        $this->setInitBuilder($initBuilder);

        $this->checkAdminDirectionAuthExists();

        $this->cssFiles = $this->getCssFiles();
        $this->jsFiles = $this->getJsFiles();
        $this->fontsFiles = \TFL::source()->config('fonts');

//        $this->cleanWebFolder();
        $this->setWebFolder();
    }

    private function setContBuilder($contBuilder)
    {
        $this->contBuilder = $contBuilder;
    }

    private function setInitBuilder(InitControllerBuilderInterface $initBuilder)
    {
        $this->routeDirection = $initBuilder->getRouteDirection();
        $this->route = $initBuilder->getSectionRoute();
        $this->routeType = $initBuilder->getSectionRouteType();
    }

    private function getTemplateName(): string
    {
        if ($this->routeDirection == InitControllerBuilder::ROUTE_ADMIN_DIRECTION) {
            return $this->routeDirection;
        }
        return self::DEFAULT_TEMPLATE;
    }

    private function getPath(): string
    {
        return zROOT . 'resource/templates/' . $this->getTemplateName() . '/';
    }

    private function getCssFiles(): array
    {
        $files = \TFL::source()->config('css');

        if ($this->isDefaultDirection()) {
            unset($files['admin']);
        } else if ($this->isAdminDirection()) {
            unset($files['template']);
        }

        return $files;
    }

    private function getJsFiles(): array
    {
        $files = \TFL::source()->config('js');

        return $files;
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

    //Првоерка доступа в админ-центр только для авторизованных
    private function checkAdminDirectionAuthExists()
    {
        if ($this->isAdminDirection()) {
            if (\TFL::source()->session->isGuest()) {
                $this->contBuilder->redirect();
            }
        }
    }

    protected function isDefaultDirection()
    {
        return $this->routeDirection == InitControllerBuilder::ROUTE_DEFAULT_DIRECTION;
    }

    protected function isAdminDirection()
    {
        return $this->routeDirection == InitControllerBuilder::ROUTE_ADMIN_DIRECTION;
    }

    protected function isApiViewDirection()
    {
        return $this->routeDirection == InitControllerBuilder::ROUTE_API_DIRECTION;
    }

}
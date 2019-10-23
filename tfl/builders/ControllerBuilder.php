<?php

namespace tfl\builders;

use tfl\interfaces\ControllerInterface;
use tfl\interfaces\InitControllerBuilderInterface;
use tfl\utils\tProtocolLoader;

/**
 * Class ControllerBuilder
 * @package tfl\builders
 *
 * @property SectionBuilder section;
 * @property InitControllerBuilderInterface initBuilder;
 */
class ControllerBuilder implements ControllerInterface
{
    private $section;
    private $initBuilder;
    private $vars = [];

    private $justAjaxRequest = false;

    public function __construct(InitControllerBuilderInterface $initBuilder)
    {
        $this->beforeAction();

        $this->initBuilder = $initBuilder;

        $route = $this->initBuilder->getSectionRoute();
        $routeType = $this->initBuilder->getSectionRouteType();

        $this->section = new SectionBuilder($route, $routeType);
    }

    protected function beforeAction(): void
    {
        $this->checkJustAjaxRequest();
    }

    private function checkJustAjaxRequest()
    {
        if ($this->justAjaxRequest && !\TFL::source()->request->isAjaxRequest()) {
            tProtocolLoader::closeAccess();
        }
    }

    /**
     * Запросы на контроллер возможны только через ajax
     */
    protected function justAjaxRequest()
    {
        $this->justAjaxRequest = true;
    }

    public function addAssignVars(array $vars = []): void
    {
        $this->section->addAssignVars($vars);
    }

    public function addComputeVars(array $vars = []): void
    {
        $this->section->addComputeVars($vars);
    }

    public function render(): string
    {
        return $this->section->renderSection();
    }

    public function redirect($url = null): void
    {
        if (!$url) $url = ROOT;

        header('Location: ' . $url);
        exit;
    }
}
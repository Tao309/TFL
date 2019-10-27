<?php

namespace tfl\builders;

use tfl\interfaces\ControllerInterface;
use tfl\interfaces\InitControllerBuilderInterface;
use tfl\observers\ControllerBuilderObserver;
use tfl\units\Unit;
use tfl\units\UnitOption;
use tfl\utils\tProtocolLoader;

/**
 * Class ControllerBuilder
 * @package tfl\builders
 *
 * @property SectionBuilder section;
 * @property string sectionRoute;
 * @property string sectionRouteType;
 * @property string routeDirection;
 */
class ControllerBuilder implements ControllerInterface
{
    use ControllerBuilderObserver;

    private $section;

    private $sectionRoute;
    private $sectionRouteType;
    private $routeDirection;

    /**
     * Доступ только через ajax запрос
     * @var bool
     */
    private $justAjaxRequest = false;
    /**
     * Проверка доступа, только для не авторизованных пользователей
     * @var bool
     */
    private $checkNoAuthRequired = false;
    /**
     * Проверка доступа, только для авторизованных пользователей
     * @var bool
     */
    private $checkAuthRequired = false;

    public function __construct(InitControllerBuilderInterface $initBuilder)
    {
        $this->section = new SectionBuilder($this, $initBuilder);

        $this->sectionRoute = $initBuilder->getSectionRoute();
        $this->sectionRouteType = $initBuilder->getSectionRouteType();
        $this->routeDirection = $initBuilder->getRouteDirection();

        $this->beforeAction();
    }

    public function getSectionRoute()
    {
        return $this->sectionRoute;
    }

    public function getSectionRouteType()
    {
        return $this->sectionRouteType;
    }

    public function getRouteDirection()
    {
        return $this->routeDirection;
    }

    protected function beforeAction(): void
    {
        $this->checkJustAjaxRequest();
        $this->checkAuthOrNotRequire();
    }

    /**
     * Контроллер используется для UnitOption
     * @param UnitOption $model
     */
    protected function appendOptionModel(UnitOption $model)
    {
        $this->section->appendOptionModel($model);
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
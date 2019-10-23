<?php

namespace tfl\builders;

use tfl\interfaces\ControllerInterface;
use tfl\interfaces\InitControllerBuilderInterface;

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

    public function __construct(InitControllerBuilderInterface $initBuilder)
    {
        $this->initBuilder = $initBuilder;

        $route = $this->initBuilder->getSectionRoute();
        $routeType = $this->initBuilder->getSectionRouteType();

        $this->section = new SectionBuilder($route, $routeType);
    }

    public function addAssignVars(array $vars = [])
    {
        $this->section->addAssignVars($vars);
    }

    public function addComputeVars(array $vars = [])
    {
        $this->section->addComputeVars($vars);
    }

    public function render()
    {
        return $this->section->renderSection();
    }
}
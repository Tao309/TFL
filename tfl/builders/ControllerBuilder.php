<?php

namespace tfl\builders;

use tfl\interfaces\ControllerInterface;

/**
 * Class ControllerBuilder
 * @package tfl\builders
 *
 * @property SectionBuilder section;
 */
class ControllerBuilder implements ControllerInterface
{
    private $section;

    public function __construct()
    {
        $route = \TFL::source()->section->getSectionRoute();
        $routeType = \TFL::source()->section->getSectionRouteType();

        $this->section = new SectionBuilder($route, $routeType);
    }

    public function render()
    {
        return $this->section->renderSection();
    }
}
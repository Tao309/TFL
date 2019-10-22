<?php

namespace tfl\builders;

/**
 * Class InitControllerBuilder
 * @package tfl\builders
 *
 * @property RequestBuilder request
 * @property string sectionRoute
 * @property string sectionRouteType
 */
class InitControllerBuilder
{
    const SUFFIX = 'Controller';
    const PREFIX_SECTION = 'section';
    const DEFAULT_ROUTE = 'index';
    const DEFAULT_ROUTE_TYPE = 'index';
    const NAME_SECTION_ROUTE = 'sectionRoute';
    const NAME_SECTION_ROUTE_TYPE = 'sectionRouteType';
    private $sectionRoute;
    private $sectionRouteType;

    public function __construct()
    {

    }

    private function getPath(): string
    {
        return zROOT . 'app/controllers/';
    }

    private function sectionRoute()
    {
        return \TFL::source()->request->getRequestValue('get', self::NAME_SECTION_ROUTE) ?? self::DEFAULT_ROUTE;
    }

    private function sectionRouteType()
    {
        return \TFL::source()->request->getRequestValue('get', self::NAME_SECTION_ROUTE_TYPE) ?? self::DEFAULT_ROUTE;
    }

    public function launch(): void
    {
        $this->sectionRoute = $this->sectionRoute();
        $this->sectionRouteType = $this->sectionRouteType();

        if (isset($_GET[self::NAME_SECTION_ROUTE])) unset($_GET[self::NAME_SECTION_ROUTE]);
        if (isset($_GET[self::NAME_SECTION_ROUTE_TYPE])) unset($_GET[self::NAME_SECTION_ROUTE_TYPE]);

        $className = ucfirst($this->sectionRoute) . self::SUFFIX;

        $file = $this->getPath() . mb_strtolower($this->sectionRoute) . '/' . $className . '.php';

        if (!file_exists($file)) {
            $message = 'Not found Controller ' . $this->sectionRoute . '::' . $className;
            throw new \tfl\exceptions\TFLNotFoundControllerException($message);
        }

        require_once $file;

        $fullClassName = 'app\\controllers\\' . $className;
        $modelController = new $fullClassName();

        $route = self::PREFIX_SECTION . ucfirst($this->sectionRouteType);

        echo $modelController->$route();
    }

    public function getSectionRoute()
    {
        return $this->sectionRoute;
    }

    public function getSectionRouteType()
    {
        return $this->sectionRouteType;
    }
}
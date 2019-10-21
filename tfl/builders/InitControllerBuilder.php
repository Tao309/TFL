<?php

namespace tfl\builders;

class InitControllerBuilder
{
    const SUFFIX = 'Controller';
    const PREFIX_SECTION = 'section';
    const DEFAULT_ROUTE = 'index';
    const DEFAULT_ROUTE_TYPE = 'index';
    const NAME_SECTION_ROUTE = 'sectionRoute';
    const NAME_SECTION_ROUTE_TYPE = 'sectionRouteType';

    public function __construct()
    {
        $this->launch();
    }

    private function getPath(): string
    {
        return zROOT . 'app/controllers/';
    }

    private function getSectionRoute()
    {
        return \TFL::source()->request->getRequestValue('get', self::NAME_SECTION_ROUTE) ?? self::DEFAULT_ROUTE;
    }

    private function getSectionRouteType()
    {
        return \TFL::source()->request->getRequestValue('get', self::NAME_SECTION_ROUTE_TYPE) ?? self::DEFAULT_ROUTE;
    }

    private function launch(): void
    {
        $sectionRoute = $this->getSectionRoute();
        $sectionRouteType = $this->getSectionRouteType();

        if (isset($_GET[self::NAME_SECTION_ROUTE])) unset($_GET[self::NAME_SECTION_ROUTE]);
        if (isset($_GET[self::NAME_SECTION_ROUTE_TYPE])) unset($_GET[self::NAME_SECTION_ROUTE_TYPE]);

        $className = ucfirst($sectionRoute) . self::SUFFIX;

        $file = $this->getPath() . mb_strtolower($sectionRoute) . '/' . $className . '.php';

        if (!file_exists($file)) {
            $message = 'Not found Controller ' . $sectionRoute . '::' . $className;
            throw new \tfl\exceptions\TFLNotFoundControllerException($message);
        }

        require_once $file;

        $fullClassName = 'app\\controllers\\' . $className;
        $modelController = new $fullClassName();

        $route = self::PREFIX_SECTION . ucfirst($sectionRouteType);

        echo $modelController->$route();
    }
}
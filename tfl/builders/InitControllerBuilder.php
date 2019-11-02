<?php

namespace tfl\builders;

use tfl\interfaces\InitControllerBuilderInterface;
use tfl\utils\tString;

/**
 * Class InitControllerBuilder
 * @package tfl\builders
 *
 * @property RequestBuilder request
 * @property string routeDirection
 * @property string sectionRoute
 * @property string sectionRouteType
 */
class InitControllerBuilder implements InitControllerBuilderInterface
{
    const SUFFIX = 'Controller';
    const PREFIX_SECTION = 'section';
    const DEFAULT_ROUTE = 'index';

    const ROUTE_DEFAULT_DIRECTION = 'site';
    const ROUTE_ADMIN_DIRECTION = 'admin';
    const ROUTE_API_DIRECTION = 'api';

    const NAME_SECTION_ROUTE_DIRECTION = 'routeDirection';
    const NAME_SECTION_ROUTE = 'sectionRoute';
    const NAME_SECTION_ROUTE_TYPE = 'sectionRouteType';

    private $routeDirection;
    private $sectionRoute;
    private $sectionRouteType;

    public function __construct()
    {
        $this->launch();
    }

    private function getPath(): string
    {
        return zROOT . 'app/controllers/';
    }

    private function routeDirection()
    {
        return \TFL::source()->request->getRequestValue('get', self::NAME_SECTION_ROUTE_DIRECTION) ??
            self::ROUTE_DEFAULT_DIRECTION;
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
        //Открытая часть, админка или api
        $this->routeDirection = $this->routeDirection();
        //index, user, option
        $this->sectionRoute = $this->sectionRoute();
        //index, list
        $this->sectionRouteType = $this->sectionRouteType();

        if (isset($_GET[self::NAME_SECTION_ROUTE_DIRECTION])) unset($_GET[self::NAME_SECTION_ROUTE_DIRECTION]);
        if (isset($_GET[self::NAME_SECTION_ROUTE])) unset($_GET[self::NAME_SECTION_ROUTE]);
        if (isset($_GET[self::NAME_SECTION_ROUTE_TYPE])) unset($_GET[self::NAME_SECTION_ROUTE_TYPE]);

        $className = $this->getClassName(ucfirst($this->sectionRoute));

        $file = $this->getPath() . mb_strtolower($this->sectionRoute) . '/' . $className . '.php';
        $route = self::PREFIX_SECTION . ucfirst($this->sectionRouteType);

        if (!file_exists($file)) {
//            @todo Добавить exception
//            $message = 'Not found Controller ' . $this->sectionRoute . '::' . $className;
//            throw new \tfl\exceptions\TFLNotFoundControllerException($message);

            list($file, $className, $route) = $this->getDefaultControllerData();
        }

        require_once $file;

        /**
         * $modelController extends ControllerBuilder
         */
        $fullClassName = 'app\\controllers\\' . $className;
        $modelController = new $fullClassName($this);

        echo $modelController->$route(...$this->getInputArgs());
    }

    /**
     * Выводим массив аргументов, передаваемых в строке
     * @return array
     */
    private function getInputArgs(): array
    {
        if (isset($_GET) && !empty($_GET)) {
            return array_values(array_map(function ($value) {
                if (is_int($value)) {
                    return tString::checkNum($value);
                }

                return tString::checkString($value);
            }, $_GET));
        }

        return [];
    }
    private function getClassName(string $name): string
    {
        $className = $name;
        if ($this->routeDirection == self::ROUTE_ADMIN_DIRECTION) {
            $className .= ucfirst($this->routeDirection);
        }
        $className .= self::SUFFIX;

        return $className;
    }

    private function getDefaultControllerData()
    {
        $nameRoute = lcfirst(self::DEFAULT_ROUTE);

        $className = $this->getClassName($nameRoute);
        $file = $this->getPath() . 'index/' . $className . '.php';
        $route = self::PREFIX_SECTION . $nameRoute;

        return [$file, $className, $route];
    }

    public function getRouteDirection()
    {
        return $this->routeDirection;
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
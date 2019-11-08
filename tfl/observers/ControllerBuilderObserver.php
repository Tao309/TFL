<?php

namespace tfl\observers;

use app\models\Page;
use tfl\builders\DbBuilder;
use tfl\builders\InitControllerBuilder;
use tfl\builders\RequestBuilder;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\units\UnitOption;
use tfl\utils\tCrypt;
use tfl\utils\tDebug;
use tfl\utils\tProtocolLoader;
use tfl\view\View;

trait ControllerBuilderObserver
{
    /**
     * Значения для замены в meta данных
     * @var array
     */
    private $seoModelValues = [];

	private function checkPartitionAccess()
	{
		//@todo Требуется фиксация. После наполнения большего функционала
		if (
			$this->getRouteDirection() == InitControllerBuilder::ROUTE_ADMIN_DIRECTION
			&& $this->getSectionRoute() != InitControllerBuilder::DEFAULT_ROUTE
		) {
			if ($this->getSectionRoute() == 'option') {
				$name = 'app\models\\' . $this->getSectionRoute() . '\\' . $this->getSectionRouteType();
			} else {
				$name = $this->getSectionRoute();
			}
			if (!\TFL::source()->partition->hasAccess(Unit::createNullModelByName($name))) {
				die('Has no access');
			}
		}
	}

    private function checkJustAjaxRequest($forceMethod = null)
    {
        if ($this->justAjaxRequest && !\TFL::source()->request->isAjaxRequest($forceMethod)) {
            tProtocolLoader::closeAccess();
        }
    }

	private function checkCsrfValidating()
	{
		if (!tCrypt::checkCsrfRequest()) {
			tProtocolLoader::closeAccess();
		}
	}

    private function checkAuthOrNotRequire()
    {
	    if (!empty($this->checkNoAuthRequired)) {
            if (!\TFL::source()->session->isGuest()) {
                tProtocolLoader::closeAccess();
            }
	    } elseif (!empty($this->checkAuthRequired)) {
            if (!\TFL::source()->session->isUser()) {
                tProtocolLoader::closeAccess();
            }
        }
    }

    private function checkMethodAuthRequire()
    {
        if (in_array($this->getSectionRouteType(), $this->methodAuthRequired)) {
            $this->enableAuthRequired();
        } elseif (in_array($this->getSectionRouteType(), $this->methodNoAuthRequired)) {
            $this->enableNoAuthRequired();
        }
    }

    /**
     * Запросы на контроллер возможны только через ajax
     */
    protected function justAjaxRequest()
    {
        $this->justAjaxRequest = true;
    }

    /**
     * Запросы на контроллер возможны только для не авторизованных пользователей
     */
    protected function enableNoAuthRequired()
    {
        $this->checkNoAuthRequired = true;
    }

    /**
     * Запросы на контроллер возможны только для авторизованных пользователей
     */
    protected function enableAuthRequired()
    {
        $this->checkAuthRequired = true;
    }

    //@todo Перенести в другое место в будущем, где будут мета-теги
    protected function setSeoValues(Unit $model)
    {
        $this->seoModelValues = $model->getSeoValues();

        $this->trySetSeoValue('siteName', 'title');
        $this->trySetSeoValue('metaDescription', 'description');
    }

    private function trySetSeoValue($seoField, $metaField)
    {
        if (isset($this->seoModelValues[$metaField])) {
            \TFL::source()->setOptionValue(UnitOption::NAME_CORE_SEO,
                $seoField, $this->seoModelValues[$metaField]);
        }

    }
}
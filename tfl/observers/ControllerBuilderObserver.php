<?php

namespace tfl\observers;

use tfl\units\Unit;
use tfl\units\UnitOption;
use tfl\utils\tProtocolLoader;

trait ControllerBuilderObserver
{
    /**
     * Значения для замены в meta данных
     * @var array
     */
    private $seoModelValues = [];

    private function checkJustAjaxRequest()
    {
        if ($this->justAjaxRequest && !\TFL::source()->request->isAjaxRequest()) {
            tProtocolLoader::closeAccess();
        }
    }

    private function checkAuthOrNotRequire()
    {
        if ($this->checkNoAuthRequired) {
            if (!\TFL::source()->session->isGuest()) {
                tProtocolLoader::closeAccess();
            }
        } elseif ($this->checkNoAuthRequired) {
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
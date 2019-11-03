<?php

namespace tfl\observers;

use app\models\Page;
use tfl\builders\DbBuilder;
use tfl\builders\RequestBuilder;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\units\UnitOption;
use tfl\utils\tProtocolLoader;
use tfl\view\View;

trait ControllerBuilderObserver
{
    /**
     * Значения для замены в meta данных
     * @var array
     */
    private $seoModelValues = [];

    /**
     * Проверяем доступ для мартшрутов REST
     */
    private function checkRequireRequest()
    {
        $hasError = false;

        $isAjaxRequest = $this->enableREST && in_array($this->getSectionRouteType(), [
                'create',
                'save',
                'delete',
            ]);

        /**
         * @var UnitActive $modelName
         */
        $modelName = 'app\models\\' . ucfirst($this->getSectionRoute());
        $id = (int)\TFL::source()->request->getRequestValue('get', 'id');
        $model = null;

        if ($isAjaxRequest) {
            $this->justAjaxRequest();

            $this->checkJustAjaxRequest($this->getMethodByRouteType());

            switch ($this->getSectionRouteType()) {
                case 'create':
                    $model = new $modelName();
                    $model->attemptRequestCreateModel();
                    break;
                case 'save':
                    $model = $modelName::getModelByIdOrCatchError($id);
                    $model->attemptRequestSaveModel();
                    break;
                case 'delete':
                    $model = $modelName::getModelByIdOrCatchError($id);
                    $model->attemptRequestDeleteModel();
                    break;
            }
        } else {
            //Просмотр вида через метод GET
            switch ($this->getSectionRouteType()) {
                case 'index'://Список
                    $model = new $modelName;
                    $this->checkAccess(DbBuilder::TYPE_VIEW, $model);
                    $this->appendTypeView(View::TYPE_VIEW_LIST);
                    break;
                case 'add'://Добавить
                    $model = $modelName::createNullOwnerModel();
                    $this->checkAccess(DbBuilder::TYPE_INSERT, $model);
                    $this->appendTypeView(View::TYPE_VIEW_ADD);
                    break;
                case 'details'://Просмотр модели
                    if (!$id || !$model = $modelName::getById($id)) {
                        $hasError = true;
                        break;
                    }
                    $this->checkAccess(DbBuilder::TYPE_VIEW, $model);
                    break;
                case 'edit'://редактировать модель
                    if (!$id || !$model = $modelName::getById($id)) {
                        $hasError = true;
                        break;
                    }

                    $this->checkAccess(DbBuilder::TYPE_UPDATE, $model);
                    $this->appendTypeView(View::TYPE_VIEW_EDIT);
                    break;
            }

            if ($hasError) {
                $this->redirect();
            }

            if ($model) {
                $this->appendModel($model);
                $this->model = $model;
            }
        }
    }

    /**
     * Получаем Request Method по типу вхождения
     * @return string
     */
    private function getMethodByRouteType()
    {
        switch ($this->getSectionRouteType()) {
            case 'create':
                return RequestBuilder::METHOD_POST;
                break;
            case 'save':
                return RequestBuilder::METHOD_PUT;
                break;
            case 'delete':
                return RequestBuilder::METHOD_DELETE;
                break;
        }

        return RequestBuilder::METHOD_GET;
    }

    private function checkJustAjaxRequest($forceMethod = null)
    {
        if ($this->justAjaxRequest && !\TFL::source()->request->isAjaxRequest($forceMethod)) {
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
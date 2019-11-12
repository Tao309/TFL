<?php

namespace tfl\observers;

use tfl\builders\InitControllerBuilder;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\utils\tRoute;
use tfl\view\View;

trait ControllerBuilderRestObserver
{
	/**
	 * Проверяем доступ для мартшрутов REST
	 * @throws \tfl\exceptions\TFLNotFoundModelException
	 */
	private function checkRestRequest()
	{
		if (!$this->enableREST) {
			return;
		}

		if ($this->isAvailableRestRequest()) {
			$modelName = Unit::checkClassExistsByName($this->getSectionRoute());
			$id = (int)\TFL::source()->request->getRequestValue('get', 'id');

			if (\TFL::source()->request->isAjaxRequest()) {
				$this->initRestAjaxRequest($modelName, $id);
			} else {
				$this->initRestViewRequest($modelName, $id);
			}
		}

		//Для ajax запроса своя проверка внутри и выдачи ошибкик
		if ($this->restErrorExists) {
			$this->redirect();
		}
	}

	/**
	 * Правильный ли доступ по REST
	 * @return bool
	 */
	private function isAvailableRestRequest(): bool
	{
		$restRoutes = tRoute::$restDefaultRoutes;
		$method = \TFL::source()->request->getRequestMethod();

		return isset($restRoutes[$method]) && in_array($this->getSectionRouteType(), $restRoutes[$method]);
	}

	/**
	 * Проверка доступа в admin части
	 * @param $modelName
	 * @param $id
	 */
	private function initRestAjaxRequest($modelName, $id): void
	{
		$this->justAjaxRequest();

		/**
		 * @var UnitActive $modelName
		 */
		switch ($this->getSectionRouteType()) {
			case tRoute::SECTION_ROUTE_CREATE:
				$model = Unit::createNullModelByName($modelName);
				$model->attemptRequestCreateModel();
				break;
			case tRoute::SECTION_ROUTE_UPDATE:
				$model = $modelName::getModelByIdOrCatchError($id);
				$model->attemptRequestSaveModel();
				break;
			case tRoute::SECTION_ROUTE_DELETE:
				$model = $modelName::getModelByIdOrCatchError($id);
				$model->attemptRequestDeleteModel();
				break;
		}
	}

	/**
	 * Проверка доступа в открытой (site) части
	 * @param $modelName
	 * @param $id
	 */
	private function initRestViewRequest($modelName, $id): void
	{
		$defaultRoute = InitControllerBuilder::DEFAULT_ROUTE;
		if ($defaultRoute == $this->getSectionRouteType() && $defaultRoute == $this->getSectionRoute()) {
			//Главную страницу не проверяем
			$this->restErrorExists = false;
			return;
		}

		/**
		 * @var UnitActive $modelName
		 */
		$model = null;

		switch ($this->getSectionRouteType()) {
			case tRoute::SECTION_ROUTE_INDEX:
			case tRoute::SECTION_ROUTE_LIST://Список
				$model = Unit::createNullModelByName($modelName);
				$this->checkAccess($this->getSectionRouteType(), $model);
				$this->appendTypeView(View::TYPE_VIEW_LIST);

				$this->restErrorExists = false;
				break;
			case tRoute::SECTION_ROUTE_ADD://Добавить
				$model = $modelName::createNullOwnerModel();
				$this->checkAccess($this->getSectionRouteType(), $model);
				$this->appendTypeView(View::TYPE_VIEW_ADD);

				$this->restErrorExists = false;
				break;
			case tRoute::SECTION_ROUTE_VIEW://Просмотр модели
				if (!$id || !$model = $modelName::getById($id)) {
					break;
				}

				$this->checkAccess($this->getSectionRouteType(), $model);

				$this->restErrorExists = false;
				break;
			case tRoute::SECTION_ROUTE_EDIT://Редактировать модель
				if (!$id || !$model = $modelName::getById($id)) {
					break;
				}

				$this->checkAccess($this->getSectionRouteType(), $model);
				$this->appendTypeView(View::TYPE_VIEW_EDIT);

				$this->restErrorExists = false;
				break;
		}

		if ($model) {
			$this->appendModel($model);
			$this->model = $model;
		}
	}
}
<?php

namespace tfl\builders;

use tfl\collections\UnitActiveCollection;
use tfl\interfaces\ControllerInterface;
use tfl\interfaces\InitControllerBuilderInterface;
use tfl\observers\ControllerBuilderObserver;
use tfl\observers\ControllerBuilderRestObserver;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\units\UnitOption;
use tfl\utils\tAccess;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;
use tfl\utils\tResponse;
use tfl\utils\tRoute;

/**
 * Class ControllerBuilder
 * @package tfl\builders
 *
 * @property SectionBuilder $section;
 * @property string $sectionRoute;
 * @property string $sectionRouteType;
 * @property string $routeDirection;
 *
 * @property UnitActive $model;
 * @property bool $enableREST;
 * @property bool $restErrorExists;
 */
class ControllerBuilder implements ControllerInterface
{
	use ControllerBuilderObserver, ControllerBuilderRestObserver;

	private $section;

	private $sectionRoute;
	private $sectionRouteType;
	private $routeDirection;

	/**
	 * Включение доступа ControllerRestBuilderObserver->checkRestRequest()
	 * @var bool
	 */
	protected $enableREST = false;

	/*
	 * Есть ли ошибка при запросе по REST
	 * @var bool $restObserverError
	 */
	private $restErrorExists = true;

	/**
	 * Получаемая модель при запросе edit, view
	 * @var UnitActive
	 */
	protected $model;

	/**
	 * Доступ только через ajax запрос
	 * @var bool
	 */
	protected $justAjaxRequest = false;
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

	/**
	 * Методы section, доступные только авторизованным
	 * @var array
	 */
	protected $methodAuthRequired = [];
	/**
	 * Методы section, доступные только не авторизованным
	 * @var array
	 */
	protected $methodNoAuthRequired = [];

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
		$this->checkCsrfValidating();
		$this->checkAuthOrNotRequire();
		$this->checkMethodAuthRequire();

		$this->checkPartitionAccess();
		$this->checkRestRequest();
	}

	public function afterAction(): void
	{

	}

	/**
	 * Контроллер используется Unit
	 * @param UnitOption $model
	 */
	protected function appendModel(Unit $model)
	{
		$this->section->appendModel($model);
	}

	/**
	 * Выставляем тип просмотра
	 * @param UnitOption $model
	 */
	protected function appendTypeView(string $typeView)
	{
		$this->section->appendTypeView($typeView);
	}

	public function addAssignVars(array $vars = []): void
	{
		$this->section->addAssignVars($vars);
	}

	/**
	 * Проверяем доступ к моделе
	 * @param string $type
	 * @param Unit|null $model
	 */
	protected function checkAccess(string $type, Unit $model = null): void
	{
		$this->checkModelOrRedirect($model);

		$hasAccess = true;
		switch ($type) {
			case tRoute::SECTION_ROUTE_ADD:
				$hasAccess = tAccess::canAdd($model);
				break;
			case tRoute::SECTION_ROUTE_EDIT:
				$hasAccess = tAccess::canEdit($model);
				break;
			case tRoute::SECTION_ROUTE_DELETE:
				$hasAccess = tAccess::canDelete($model);
				break;
			case tRoute::SECTION_ROUTE_VIEW:
				$hasAccess = tAccess::canView($model);
				break;
		}

		if (!$hasAccess) {
			$this->redirect();
		}
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
		if (!$url) {
			$url = ROOT;

			if ($this->routeDirection == InitControllerBuilder::ROUTE_ADMIN_DIRECTION) {
				$url .= $this->routeDirection . DIR_SEP;
			}
		}

		header('Location: ' . $url);
		exit;
	}

	public function sectionModalList()
	{
		//Показ списка моделей для выбора
		//@todo оптимизировать

		$collection = new UnitActiveCollection(Unit::createNullModelByName($this->getSectionRoute()));
		$collection->setOffset(0);
		$collection->setPerPage(10);
//		$requestData = \TFL::source()->request->getRequestData(RequestBuilder::METHOD_POST);
//		$collection->setExcludeValues($requestData);

		$typeLink = \TFL::source()->request->getRequestValue(RequestBuilder::METHOD_POST, 'typeLink');
		$elementName = \TFL::source()->request->getRequestValue(RequestBuilder::METHOD_POST, 'elementName');

		$item = [];

		foreach ($collection->getModels() as $model) {
			/**
			 * @var UnitActive $model
			 */
			$htmlData = tHtmlForm::generateElementData([
				'admin/section', 'modal', 'choose',
			], RequestBuilder::METHOD_GET, [
				'class' => ['html-button', 'html-list-element']
			]);

			$htmlData .= tHtmlForm::generateDataParams([
				'id' => $model->id,
				'name' => (string)$model,
				'typeLink' => $typeLink,
				'elementName' => $elementName,
			]);

			$t = tHtmlTags::render('span', (string)$model, [
				'class' => 'title'
			]);

			$item[] = tHtmlTags::render('div', $t, $htmlData);
		}

		tResponse::modalWindow('Users List', implode(PAGE_EOL, $item), ['model-list']);
	}

	/**
	 * Если модель не существует, перенаправляем
	 * @param Unit|null $model
	 */
	private function checkModelOrRedirect(Unit $model = null)
	{
		if (!$model) {
			$this->redirect();
		}

		return;
	}
}
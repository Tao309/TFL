<?php

namespace tfl\builders;

use tfl\collections\UnitActiveCollection;
use tfl\interfaces\ControllerInterface;
use tfl\interfaces\InitControllerBuilderInterface;
use tfl\observers\ControllerBuilderObserver;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\units\UnitOption;
use tfl\utils\tAccess;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;
use tfl\utils\tResponse;
use tfl\utils\tString;

/**
 * 'section/page/'          => sectionList (GET)
 * 'section/page/add/       => sectionAdd (GET)
 * 'section/page/2/         => sectionDetails (GET)
 * 'section/page/2/edit'    => sectionEdit (GET)
 *
 * 'section/page/create/    => sectionCreate (POST)
 * 'section/page/2/save     => sectionSave (PUT)
 * 'section/page/2/delete'  => sectionDelete (DELETE)
 */
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
 */
class ControllerBuilder implements ControllerInterface
{
	use ControllerBuilderObserver;

	private $section;

	private $sectionRoute;
	private $sectionRouteType;
	private $routeDirection;
	/**
	 * Включение доступа ControllerBuilderObserver->checkRequireRequest()
	 * @var bool
	 */
	protected $enableREST = false;

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
		$this->checkRequireRequest();

		$this->checkJustAjaxRequest();
		$this->checkAuthOrNotRequire();
		$this->checkMethodAuthRequire();
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
	 * @param UnitActive|null $model
	 */
	protected function checkAccess(string $type, UnitActive $model = null): void
	{
		$this->checkModelOrRedirect($model);

		$hasAccess = true;
		switch ($type) {
			case DbBuilder::TYPE_INSERT:
				$hasAccess = tAccess::canAdd($model);
				break;
			case DbBuilder::TYPE_UPDATE:
			case DbBuilder::TYPE_SAVE:
				$hasAccess = tAccess::canEdit($model);
				break;
			case DbBuilder::TYPE_DELETE:
				$hasAccess = tAccess::canDelete($model);
				break;
			case DbBuilder::TYPE_VIEW:
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

		/**
		 * @var UnitActive $modelName
		 */
		$modelName = 'app\models\\' . ucfirst($this->getSectionRoute());

		$collection = new UnitActiveCollection(new $modelName);
		$collection->setOffset(0);
		$collection->setPerPage(10);

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
				'chooseType' => UnitActive::LINK_HAS_ONE_TO_MANY,
			]);

			$t = tHtmlTags::render('span', (string)$model, [
				'class' => 'title'
			]);

			$item[] = tHtmlTags::render('div', $t, $htmlData);
		}

		tResponse::modalWindow('Users List', implode(PAGE_EOL, $item), ['model-list']);

//	    tResponse::resultSuccess([tString::RESPONSE_OK, DbBuilder::TYPE_DELETE], true, true, $this);
//		tResponse::resultError($this->getDeleteErrors(), true, true, $this);
	}

	/**
	 * Если модель не существует, перенаправляем
	 * @param UnitActive|null $model
	 */
	private function checkModelOrRedirect(UnitActive $model = null)
	{
		if (!$model) {
			$this->redirect();
		}

		return;
	}
}
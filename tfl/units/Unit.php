<?php

namespace tfl\units;

use tfl\builders\DbBuilder;
use tfl\builders\RequestBuilder;
use tfl\builders\TemplateBuilder;
use tfl\builders\UnitBuilder;
use tfl\builders\UnitSqlBuilder;
use tfl\exceptions\TFLNotFoundModelException;
use tfl\observers\UnitObserver;
use tfl\observers\UnitRulesObserver;
use tfl\observers\UnitSqlObserver;
use tfl\repository\UnitRepository;
use tfl\utils\tProtocolLoader;
use tfl\utils\tResponse;
use tfl\utils\tString;

/**
 * Class Unit
 * @package tfl\units
 *
 * @property int $id
 * @property string $modelName
 * @property string $modelNameLower
 * @property array $modelUnitData
 * @property bool $isWasNewModel
 * @property array $oldValues
 */
abstract class Unit
{
	use UnitObserver, UnitSqlObserver, UnitBuilder, UnitSqlBuilder, UnitRepository, UnitRulesObserver;

	const DB_MODEL_PREFIX = 'model';
	const DB_TABLE_UNIT = 'unit';

	const RULE_TYPE_MODEL = 'Model';
	const RULE_TYPE_TEXT = 'Text';
	const RULE_TYPE_DATETIME = 'DateTime';
	const RULE_TYPE_DESCRIPTION = 'Description';
	const RULE_TYPE_INT = 'Integer';
	const RULE_TYPE_ARRAY = 'Array';

	/**
	 * Список нулевых моделей, уникальный
	 * @var array
	 */
	private static $nullModels = [];

	/**
	 * @var $modelName string|null
	 */
	private $modelName;
	/**
	 * @var $modelNameLower string|null
	 */
	private $modelNameLower;
	/**
	 * @var $modelUnitData array|null
	 */
	private $modelUnitData;
	/**
	 * @var bool $isWasNewModel Модели были ли изначально новая
	 */
	private $isWasNewModel;

	/**
	 * Значения после нахождения модели
	 * @var array []
	 */
	protected $oldValues;
	/**
	 * Возможность прямого сохранения
	 * @var bool
	 */
	protected $directSaveEnabled = false;

	/**
	 * Ошибки при сохранении
	 * @var array
	 */
	protected $saveErrors = [];
	/**
	 * Ошибки при удалении
	 * @var array
	 */
	protected $deleteErrors = [];
	/**
	 * Ошибки при заполнении модели из массива запроса
	 * @var array
	 */
	protected $loadDataErrors = [];

	/**
	 * Проверяем наличие названия класса
	 * @param string $sectionRoute
	 * @return string
	 * @throws TFLNotFoundModelException
	 */
	public static function checkClassExistsByName(string $sectionRoute, string $moduleName = null)
	{
		if (!strstr($sectionRoute, '\\')) {
			$modelClassName = 'app\models\\';
			if ($moduleName) {
				$modelClassName .= mb_strtolower($moduleName) . '\\';
			}
			$modelClassName .= ucfirst($sectionRoute);
		} else {
			$modelClassName = $sectionRoute;
		}

		if (!class_exists($modelClassName)) {
			throw new TFLNotFoundModelException($modelClassName);
		}

		return $modelClassName;
	}

	/**
	 * Создаём нулевую модель из названия sectionRoute
	 * @param string $name
	 * @return Unit
	 */
	public static function createNullModelByName(string $name, $forceNew = false)
	{
		$className = self::checkClassExistsByName($name);
		if ($forceNew) {
			return Unit::createNullModelByName($className);
		}

		if (!isset(self::$nullModels[$className])) {
			self::$nullModels[$className] = new $className;
		}

		return self::$nullModels[$className];
	}

	public function __construct()
	{
		$this->beforeFind();
	}

	public function __toString()
	{
		return $this->getModelName() . ' #' . $this->id;
	}

	public function isNewModel()
	{
		return !isset($this->id) || !$this->id || $this->id <= 0;
	}

	protected function setIsWasNewModel(): void
	{
		$this->isWasNewModel = true;
	}

	protected function isWasNewModel()
	{
		return $this->isWasNewModel;
	}

	public function getLabel($attr)
	{
		if ($this instanceof UnitOption) {
			return $this->getOptionList()[$attr]['title'] ?? "Option Label <b>$attr</b> not found";
		}

		return $this->translatedLabels()[$attr] ?? "Label <b>$attr</b> not found";
	}

	/**
	 * Input Model Table Name
	 *
	 * @return string
	 */
	public function getTableName(): string
	{
		return self::DB_MODEL_PREFIX . '_' . mb_strtolower($this->getModelName());
	}

	/**
	 * Input model name
	 * @return string
	 */
	public function getModelName(): string
	{
		return $this->modelName;
	}

	/**
	 * Input model name lowercase
	 * @return string
	 */
	public function getModelNameLower(): string
	{
		return $this->modelNameLower;
	}

	public function hasAttribute($attrName)
	{
		return property_exists($this, $attrName) && !empty($this->$attrName);
	}

	public function getUnitData(): array
	{
		return $this->modelUnitData;
	}

	public function getUnitDataRelations(): array
	{
		return $this->getUnitData()['relations'];
	}

	public function getUnitDataRules(): array
	{
		return $this->getUnitData()['rules'];
	}

	public function getUnitDataRelationByAttr(string $attr): array
	{
		return $this->getUnitData()['relations'][$attr] ?? [];
	}

	public function getUnitDataRule(string $attr): array
	{
		return $this->getUnitData()['rules'][$attr] ?? [];
	}

	/**
	 * Сохраняем старые значения после нахождения модели
	 */
	protected function keepOldValues(): void
	{
		$data = [
			'details' => [],
			'relations' => [],
		];

		if ($this->isNewModel()) {
			$this->oldValues = $data;
			return;
		}

		foreach ($this->getUnitData()['details'] as $attr) {
			if (empty($this->$attr)) {
				continue;
			}

			$data['details'][$attr] = $this->$attr;
		}

		foreach ($this->getUnitDataRelations() as $attr => $data) {
			if (empty($this->$attr)) {
				continue;
			}

			if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
				foreach ($this->$attr as $index => $relationModel) {
					$data['relations'][$attr][] = $relationModel->id;
				}
			} else {
				$data['relations'][$attr] = $this->$attr->id;
			}
		}

		$this->oldValues = $data;
	}

	public function getOldDetailsValues($attr = null)
	{
		if ($attr) {
			return $this->oldValues['details'][$attr] ?? null;
		}
		return $this->oldValues['details'];
	}

	public function getOldRelationsValues($attr = null)
	{
		if ($attr) {
			return $this->oldValues['relations'][$attr] ?? null;
		}

		return $this->oldValues['relations'];
	}

	/**
	 * Включаем возможность прямого сохранения через directSave()
	 */
	protected function enableDirectSave(): void
	{
		$this->directSaveEnabled = true;
	}

	protected function addSaveError(string $name, string $message): void
	{
		$this->saveErrors[$name] = $message;
	}

	public function getSaveErrors(): string
	{
		return implode(PAGE_BR, $this->saveErrors);
	}

	public function getSaveErrorsElements()
	{
		return array_map(function ($key) {
			return $this->getModelName() . '[' . $key . ']';
		}, array_keys($this->saveErrors));
	}

	protected function addDeleteError(string $name, string $message): void
	{
		$this->deleteErrors[$name] = $message;
	}

	public function getDeleteErrors(): string
	{
		return implode(PAGE_BR, $this->deleteErrors);
	}

	protected function addLoadDataError(string $message)
	{
		$this->loadDataErrors[] = $message;
	}

	public function getLoadDataErrors(): string
	{
		return implode(PAGE_BR, $this->loadDataErrors);
	}

	/**
	 * Добавление данных в tResponse при ответе
	 * @return array
	 */
	public function getResponse(string $actionType): array
	{
		return [
			'id' => $this->id ?? 0,
			'name' => $this->getModelNameLower(),
			'element_id' => $this->getHtmlElementId(),
		];
	}

	public function getHtmlElementId()
	{
		return 'model-' . $this->getModelNameLower() . '-' . ($this->id ?? 0);
	}

	/**
	 * Получаем список полей, которые не проверяем при сейве
	 * Элементы типа TemplateBuilder::VIEW_TYPE_ARRAY
	 * @return array
	 */
	public function getExcludedAsArrayAttrs(): array
	{
		$array = [];

		if ($this instanceof UnitOption || $this instanceof UnitActive) {
			foreach ($this->getUnitData()['rules'] as $index => $data) {
				if (isset($data['type']) && $data['type'] == self::RULE_TYPE_ARRAY) {
					$array[] = $index;
				}
			}
		}

		return $array;
	}

	/**
	 * Процесс создания модели при отправки запросом
	 */
	public function attemptRequestCreateModel(): void
	{
		$this->attemptRequestSaveModel(true);
	}

	/**
	 * Процесс сохранения модели при отправки запросом
	 */
	public function attemptRequestSaveModel($create = false): void
	{
		if ($create) {
			$method = RequestBuilder::METHOD_POST;
			$action = DbBuilder::TYPE_INSERT;
		} else {
			$method = RequestBuilder::METHOD_PUT;
			$action = DbBuilder::TYPE_SAVE;
		}

		if (\TFL::source()->request->isAjaxRequest()) {
			if (\TFL::source()->request->checkForceMethod($method)) {
				if ($this->attemptLoadData()) {
					if ($this->save()) {
						tResponse::resultSuccess($action, true, true, $this);
					} else {
						tResponse::resultError($this->getSaveErrors(), true, true, $this);
					}
				} else {
					tResponse::resultError($this->getLoadDataErrors(), true, true, $this);
				}
			}

			tProtocolLoader::closeAccess();
		}
	}

	/**
	 * Процесс удаления модели при отправки запросом
	 */
	public function attemptRequestDeleteModel(): void
	{
		if (\TFL::source()->request->isAjaxRequest()) {
			if (\TFL::source()->request->checkForceMethod(RequestBuilder::METHOD_DELETE)) {
				if ($this->delete()) {
					tResponse::resultSuccess(DbBuilder::TYPE_DELETE, true, true, $this);
				} else {
					tResponse::resultError($this->getDeleteErrors(), true, true, $this);
				}
			}

			tProtocolLoader::closeAccess();
		}
	}
}

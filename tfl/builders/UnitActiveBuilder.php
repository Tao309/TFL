<?php

namespace tfl\builders;

use app\models\Image;
use app\models\Page;
use app\models\User;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\utils\tString;

trait UnitActiveBuilder
{
	private $rowDataForCreateFinalModel = [];

	/**
	 * Распределяем данные из request по атрибутам модели
	 * @param array $request
	 */
	protected function setAttrsFromRequestData(array $request): void
	{
		$excludedAsArrayAttrs = $this->getExcludedAsArrayAttrs();

		foreach ($this->getUnitData()['details'] as $attr) {
			if (!isset($request[$attr])) {
				continue;
			}

			if (in_array($attr, $excludedAsArrayAttrs)) {
				$this->$attr = tString::serialize($request[$attr]);
			} else {
				$this->$attr = tString::encodeString($request[$attr]);
			}
		}
	}

	/**
	 * Подставляем данные из $_FILES
	 */
	protected function setAttrsFromFilesData(): void
	{
		$request = \TFL::source()->request->getRequestData(RequestBuilder::METHOD_FILES);

		if (!empty($request)) {
			foreach ($this->getUnitData()['details'] as $attr) {
				if (!isset($request[$attr])) {
					continue;
				}

				$this->$attr = $request[$attr];
			}
		}
	}

	protected function setRelationsForNewModelFromRequestData(array $request): void
	{
		foreach ($this->getUnitDataRelations() as $attr => $data) {
			if (!isset($request[$attr])) {
				continue;
			}

			if ($data['link'] == self::LINK_HAS_ONE_TO_MANY) {
				$this->$attr = [];

				foreach ($request[$attr] as $index => $value) {
					$this->$attr[] = tString::encodeValue($value);
				}
			} else if ($data['link'] == self::LINK_HAS_ONE_TO_ONE) {
				$value = tString::encodeValue($request[$attr]);

				$this->$attr = $value;
			}
		}
	}

	/**
	 * Распределяем данные из request по relations модели
	 * @param array $request
	 */
	protected function setRelationsFromRequestData(array $request): void
	{
		foreach ($this->getUnitData()['relations'] as $attr => $data) {
			if (!isset($request[$attr])) {
				if (!$this->isNewModel()) {
					if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
						$this->$attr = [];
					} else {
						$this->$attr = null;
					}
				}

				continue;
			}

			if ($data['model'] === UnitActive::class) {
				$attrNames = ['id', 'name', 'attr'];

				$hasError = false;
				foreach ($attrNames as $attrName) {
					if (!isset($request[$attr][$attrName])) {
						$hasError = true;
						$this->addSaveError('model', 'Field ' . $attrName . ' for model UnitActive is not found');
						break;
					}

					if ($attrName == 'id') {
						$attrValue = tString::encodeNum($request[$attr][$attrName]);
					} else {
						$attrValue = tString::encodeString($request[$attr][$attrName]);
					}

					$this->{$attr . '_' . $attrName} = $attrValue;
				}

				if ($hasError) {
					break;
				}

				if (!isset($request['model']['name'])) {
					$this->addSaveError('model', 'Model name is not found');
					break;
				}

				switch ($request['model']['name']) {
					case 'user':
						$modelClass = User::class;
						break;
					case 'page':
						$modelClass = Page::class;
						break;
				}

				if ($this->isNewModel()) {
					/**
					 * @var UnitActive $modelClass
					 */
					//@todo Сделать одним запросом все получения
					if ($this->{$attr . '_id'} > 0) {
						$this->$attr = $modelClass::getById($this->{$attr . '_id'});
					} else {
						//Загрузка изображения при создании страницы, новости
						$this->$attr = new $modelClass(true);
//                        $this->$attr = $modelClass::createNullOwnerModel();
					}
				}
			} else {
				$modelClass = $data['model'];
				$this->$attr = $request[$attr];
			}
		}
	}

	public function createFinalModel(Unit $model, array $rowData, $isPrimaryModel = false, $skipRelation = false)
	{
		$this->rowDataForCreateFinalModel = $rowData;
		/**
		 * @var $model UnitActive
		 */
		$model->setAttributes($model);
		if (!$skipRelation) {
			$model->setRelations($model);
		}

		if ($isPrimaryModel) {
			$model->setOwner($model);
		}

		unset($this->rowDataForCreateFinalModel);

		$this->afterFind();

		return $model;
	}

	private function setAttributes(Unit $model): void
	{
		$rowData = $this->rowDataForCreateFinalModel;

		if ($model->isNulOwnerModel()) {
			$model->id = 0;
		} else {
			$model->id = $rowData['id'];
		}

		$model->createdDateTime = $rowData['createddatetime'] ?? null;
		$model->lastChangeDateTime = $rowData['lastchangedatetime'] ?? null;

		$rules = $model->getUnitDataRules();
		$excludedAsArrayAttrs = $this->getExcludedAsArrayAttrs();

		foreach ($model->getUnitData()['details'] as $index => $attr) {
			if (isset($rules[$attr]['secretField'])) {
				continue;
			}

			$lowAttr = mb_strtolower($attr);

			if (in_array($attr, $excludedAsArrayAttrs)) {
				if (empty($rowData[$lowAttr])) {
					$value = [];
				} else {
					$value = tString::unserialize($rowData[$lowAttr]);
				}
			} else {
				$value = $rowData[$lowAttr] ?? null;
			}

			$model->$attr = $value;
		}

		foreach ($this->getUnitData()['relations'] as $attr => $data) {
			//Добавления для столбцов где UnitActive, а не точная модель
			if ($data['type'] == static::RULE_TYPE_MODEL && $data['model'] == UnitActive::class) {
				$attr = mb_strtolower($attr);
				$model->{$attr . '_name'} = $rowData[$attr . '_name'] ?? null;
				$model->{$attr . '_id'} = $rowData[$attr . '_id'] ?? null;
				$model->{$attr . '_attr'} = $rowData[$attr . '_attr'] ?? null;
			}
		}
	}

	private function setOwner(Unit $model)
	{
		if ($this->isNulOwnerModel()) {
			return;
		}

		$owner = new User();
		$owner->createFinalModel($owner, $this->rowDataForCreateFinalModel['owner']);

		$model->owner = $owner;
	}

	private function setRelations(Unit $model)
	{
		$rowData = $this->rowDataForCreateFinalModel;
		foreach ($model->getUnitData()['relations'] as $attr => $data) {
			if ($data['type'] == static::RULE_TYPE_MODEL && isset($data['model'])) {
				if (!isset($rowData['relations'][$attr])) {
					$model->$attr = null;
					continue;
				}

				if ($data['link'] == static::LINK_HAS_ONE_TO_MANY) {
					$model->$attr = [];
					foreach ($rowData['relations'][$attr] as $index => $row) {
						$model->$attr[] = $this->setRelationOneModel($model, $data, $attr, $row);
					}
				} else if ($data['link'] == static::LINK_HAS_ONE_TO_ONE) {
					$model->$attr = $this->setRelationOneModel($model, $data, $attr, $rowData['relations'][$attr]);
				}
			}
		}
	}

	private function setRelationOneModel(Unit $model, array $data, string $attr, array $rowData)
	{
		/**
		 * @var UnitActive $relationModel
		 */
		$relationModel = Unit::createNullModelByName($data['model'], true);

		//Добавление зависимых моделей в модели связи
		if ($relationModel instanceof Image) {
			$relationModel->model_name = $model->getModelNameLower();
			$relationModel->model_id = $model->id;
			$relationModel->model_attr = $attr;
			$relationModel->model = $model;
		}

		return $relationModel->createFinalModel($relationModel, $rowData, true, true);
	}

}

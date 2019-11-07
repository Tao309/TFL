<?php

namespace tfl\observers;

use app\models\User;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\utils\tString;

trait UnitSqlObserver
{
	private function saveModelAttrs(): bool
	{
		//@todo Добавить проверку атрибутов
		list($attrs, $values) = $this->getAttrAndValuesForSave();

		if (empty($attrs)) {
			$this->addSaveError('attributes', 'Not found attributes');
			return false;
		}

		if ($this->isNewModel()) {
			\TFL::source()->db->insert($this->getTableName(), array_combine($attrs, $values));

			$id = \TFL::source()->db->getLastInsertId();

			$this->id = $id;
			$this->setIsWasNewModel();
		} else {
			$excludeCheck = $this->getExcludedAsArrayAttrs();

			\TFL::source()->db->update($this->getTableName(), array_combine($attrs, $values), [
				'id' => $this->id,
			], $excludeCheck);
		}

		return true;
	}

	private function getAttrAndValuesForSave(): array
	{
		$attrs = $values = $sliceValues = [];

		$rules = $this->getUnitData()['rules'];

		foreach ($this->getUnitData()['details'] as $attr) {
			$value = $this->$attr ?? null;

			if (isset($rules[$attr]['secretField']) && empty($value)) {
				continue;
			}

			$attrs[] = $attr = mb_strtolower($attr);


			if (isset($rules[$attr]) && $rules[$attr]['type'] == static::RULE_TYPE_DESCRIPTION) {
				tString::fromTextareaToDb($value);
			}

			$values[] = $value;
		}

		foreach ($this->getUnitData()['relations'] as $attr => $data) {
			if ($data['type'] == static::RULE_TYPE_MODEL) {
				if ($data['model'] == UnitActive::class) {
					$attr = mb_strtolower($attr);

					$attr_id = $attr . '_id';
					$attrs[] = $attr_id;
					$values[] = $this->$attr_id;

					$attr_name = $attr . '_name';
					$attrs[] = $attr_name;
					$values[] = $this->$attr_name;

					$attr_attr = $attr . '_attr';
					$attrs[] = $attr_attr;
					$values[] = $this->$attr_attr;
				}
			}

		}

		return [$attrs, $values];
	}

	protected function saveModelUnit(): bool
	{
		$dateTime = date('Y-m-d H:i:s');

		$ownerId = User::ID_USER_SYSTEM;
		if (\TFL::source()->session->isUser()) {
			$ownerId = \TFL::source()->session->currentUser()->id;
		}

		$data = [
			'model_name' => $this->getModelNameLower(),
			'model_id' => $this->id,
			'createddatetime' => $dateTime,
			'lastchangedatetime' => $dateTime,
			'owner_id' => $ownerId,
		];

		\TFL::source()->db->insert(static::DB_TABLE_UNIT, $data, [
			'lastchangedatetime'
		]);

		return true;
	}

	protected function saveModelRelations(): bool
	{
		$update = [];

		foreach ($this->getUnitDataRelations() as $attr => $data) {
			if (!isset($this->$attr)) {
				continue;
			}

			if ($data['model'] === UnitActive::class) {
				continue;
			}

			/**
			 * @var UnitActive $model
			 */
			$model = Unit::createNullModelByName($data['model']);
			if ($model->isDependModel()) {
				//Пропускаем зависимые модели, f.e Image, они сохраняются при загрузке своей модели сами
				continue;
			}

			$updateIndex = $data['model'] . '\\' . $data['link'];
			if (!isset($update[$updateIndex])) {
				if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
					$attrName = $this->getModelNameLower() . '_id';
				} else {
					$attrName = $attr . '_id';
				}

				$update[$updateIndex] = [
					'attrName' => $attrName,
					'link' => $data['link'],
					'model' => $model,
					'ids' => [],
					'oldIds' => [],
				];
			}
			//Добавляем значения изначальные
			$oldIds = $this->getOldRelationsValues($attr);
			if (!empty($oldIds)) {
				if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
					$update[$updateIndex]['oldIds'] = array_merge($update[$updateIndex]['oldIds'], $oldIds);
				} else {
					$update[$updateIndex]['oldIds'][] = $oldIds;
				}

				$update[$updateIndex]['oldIds'] = array_unique($update[$updateIndex]['oldIds']);
			}

			if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
				foreach ($this->$attr as $id) {
					$valueId = ($id instanceof UnitActive) ? $id->id : $id;
					$update[$updateIndex]['ids'][] = $valueId;
				}
			} else if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_ONE) {
				$valueId = ($this->$attr instanceof UnitActive) ? $this->$attr->id : $this->$attr;
				$update[$updateIndex]['ids'][] = $valueId;
			}
		}

		foreach ($update as $updateIndex => $data) {
			$newIds = array_diff($data['ids'], $data['oldIds']);
			$oldDeleteIds = array_diff($data['oldIds'], $data['ids']);

			/**
			 * @var UnitActive $data ['model']
			 */
			$model = $data['model'];

			if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
				//Обнуляем старые значения
				if (!empty($oldDeleteIds)) {
					\TFL::source()->db->update($model->getTableName(), [
						$data['attrName'] => 0,
					], [
						['id', 'IN', $oldDeleteIds],
					]);
				}

				//Записываем новые значения
				if (!empty($newIds)) {
					\TFL::source()->db->update($model->getTableName(), [
						$data['attrName'] => $this->id,
					], [
						['id', 'IN', $newIds],
					]);
				}
			} else {
				if (empty($newIds)) {
					continue;
				}

				//Записываем новые значения
				\TFL::source()->db->update($this->getTableName(), [
					$data['attrName'] => $newIds[0],
				], [
					'id' => $this->id,
				]);
			}
		}

		return true;
	}

	protected function deleteModel()
	{
		\TFL::source()->db->delete($this->getTableName(), [
			'id' => $this->id
		]);

		return true;
	}

	protected function deleteModelUnit()
	{
		\TFL::source()->db->delete(static::DB_TABLE_UNIT, [
			'model_name' => $this->getModelNameLower(),
			'model_id' => $this->id,
		]);

		return true;
	}
}
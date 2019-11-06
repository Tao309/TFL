<?php

namespace tfl\observers;

use app\models\User;
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
			if (isset($rules[$attr]['secretField'])) {
				continue;
			}

			$attrs[] = $attr = mb_strtolower($attr);

			$value = $this->$attr ?? null;

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
			if (!$this->hasAttribute($attr)) {
				continue;
			}

			if (!isset($update[$data['model']])) {
				if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
					$attrName = $this->getModelNameLower() . '_id';
				} else {
					$attrName = $attr . '_id';
				}

				$update[$data['model']] = [
					'ids' => [],
					'attrName' => $attrName,
					'attr' => $attr,
					'clearIds' => [],//По этому массиву обнулять модели, что были ранее
				];
			}

			if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
				foreach ($this->$attr as $id) {
					$update[$data['model']]['ids'][] = $id;
				}
			} else if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_ONE) {
				$update[$data['model']]['ids'][] = $this->$attr;
			}

			//Значения, которые надо обнулить
			$oldIds = $this->getOldRelationsValues($attr);
			if (!empty($oldIds)) {
				$clearIds = array_diff($oldIds, $update[$data['model']]['ids']);
				if (!empty($clearIds)) {
					$update[$data['model']]['clearIds'] = array_merge($update[$data['model']]['clearIds'], $clearIds);
				}
			}
		}

		if (!$this->isWasNewModel()) {
			//Сохраняем уже имеющуюся модель, relation модели до нового сохранения открепить
			//Нужны oldValues или что-то такое
		}

		//Обновление только для изображений
		foreach ($update as $modelName => $data) {
			/**
			 * @var UnitActive $model ;
			 */
			$model = new $modelName;

			if (!$model->isDependModel()) {
				//Сбрасываем ранее сохранённые
				if (!empty($data['clearIds'])) {
					\TFL::source()->db->update($model->getTableName(), [
						$data['attrName'] => 0,
					], [
						['id', 'IN', $data['clearIds']],
					]);
				}
			}

			//Записываем новые сохранённые
			\TFL::source()->db->update($model->getTableName(), [
				$data['attrName'] => $this->id,
			], [
				['id', 'IN', $data['ids']]
			]);
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
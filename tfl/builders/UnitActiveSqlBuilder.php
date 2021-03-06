<?php

namespace tfl\builders;

use app\models\Image;
use app\models\User;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\utils\tDebug;
use tfl\utils\tString;

trait UnitActiveSqlBuilder
{
	/**
	 * Получаем общее количество моделей
	 * @return int
	 */
	public function getCount()
	{
		//@todo В будущем внести исправления при работе с WHERE в разделах при фильтрации
		return \TFL::source()->db->from($this->getTableName())->getCount();
	}

	/**
	 * @param array $queryData
	 * @param array $option Настройки для запроса
	 * @return array
	 */
	public function prepareRowData(array $queryData = [], $option = []): array
	{
		if (empty($queryData)) {
			return [];
		}

		//@todo Сделать красиво ниже. Добавить значения по умолчанию
		$many = $option['many'] ?? false;
		$skipOwner = $option['skipOwner'] ?? false;
		$skipRelations = $option['skipRelations'] ?? false;
		$offset = isset($option['offset']) ? tString::encodeNum($option['offset']) : 0;
		//@todo addDefault perPage for all
		$perPage = isset($option['perPage']) ? tString::encodeNum($option['perPage']) : 30;
		$order = isset($option['order']) ? tString::encodeString($option['order']) : null;
		$orderType = isset($option['orderType']) ? tString::encodeString($option['orderType']) : null;
		$where = isset($option['where']) && is_array($option['where']) ? $option['where'] : [];

		if (in_array(['id', 'password', 'name', 'unitcollection', 'nullmodel'], array_keys($queryData))) {
			return null;
		}

		$isCollection = isset($queryData['unitcollection']);
		$isNullOwnerModel = isset($queryData['nullmodel']);

		$this->setDefaultLinkType();

		$tableName = $this->getTableName();

		$command = \TFL::source()->db;

		if ($isNullOwnerModel) {
			$this->setNulOwnerQueryFromInputData($command);
		} else {
			$command->select(implode(',', $this->getModelColumnAttrs($tableName)));
			$command->from($tableName);
		}

		if (!$isCollection && !$isNullOwnerModel) {
			$this->setQueryFromInputData($command, $queryData, $many);
		}

		$this->addUnitQuery($command, $tableName);
		if (!$skipOwner) {
			$this->addOwnerQuery($command);
		}

		if (!$skipRelations) {
			$this->addRelationsQuery($command);
		}

		if (!empty($where)) {
			$command->andWhere($where);
		}

		if ($many) {
			if ($order) {
				$command->order($order, $orderType);
			}

			$command->limit($offset, $perPage);

			$rows = $command->findAll();

			if (empty($rows)) {
				return [];
			}

			$rows = array_map(function ($row) {
				$this->assignRowData($row);
				$this->assignRelationsData($row);

				return $row;
			}, $rows);

			return $rows;
		} else {

//            tDebug::printDebug($command->getText());
			$row = $command->find();

			if (empty($row)) {
				return null;
			}
//			tDebug::printDebug($row);

			$this->assignRowData($row);
			if (!$skipRelations) {
				$this->assignRelationsData($row);
			}


			return $row;
		}
	}

	private function setNulOwnerQueryFromInputData(DbBuilder $command)
	{
		$command->select(static::DB_MODEL_PREFIX . '_user.id = 0');
		$command->from(static::DB_MODEL_PREFIX . '_user');
		$command->where(static::DB_MODEL_PREFIX . '_user.id = 1');

	}

	private function addUnitQuery(DbBuilder &$command, $userTableName, $tableName = null, $encase = false)
	{
		if ($this->isNulOwnerModel()) {
			return;
		}

		$selectTable = $tableName ?? 'unit';
		$tableNameJoin = static::DB_TABLE_UNIT;

		if ($encase) {
			$selectTable = '`' . $tableName . '.' . static::DB_TABLE_UNIT . '`';
			$tableNameJoin .= ' AS ' . $selectTable;
		}

		$attrs = [];
		$availableAttrs = [
			'createddatetime',
			'lastchangedatetime',
		];
		foreach ($availableAttrs as $availableAttr) {
			$attrs[] = $this->concatAttr($availableAttr, $selectTable, $tableName, $encase);
		}

		$command->addSelect(implode(',', $attrs))
			->leftJoin($tableNameJoin, [
				$selectTable . '.model_id' => $userTableName . '.id',
				$selectTable . '.model_name' => '"' . $this->getModelNameLower() . '"'
			]);
	}

	private function addOwnerQuery(DbBuilder &$command, $aliasTable = null)
	{
		if ($this->isNulOwnerModel()) {
			return;
		}

		$unitTableAlias = (!empty($aliasTable)) ? '`' . $aliasTable . '.unit`' : static::DB_TABLE_UNIT;
		$aliasTable = (!empty($aliasTable)) ? $aliasTable . '.owner' : 'owner';

		$aliasTableEncase = "`" . $aliasTable . "`";

		//@todo исправить, добавить в переменную
		$model = Unit::createNullModelByName(User::class);
		$model->setNewLinkType($this->linkType);

		$attrs = $model->getModelColumnAttrs($aliasTable, true);

		$command->addSelect(implode(',', $attrs))
			->leftJoin("model_user AS " . $aliasTableEncase, [
				$aliasTableEncase . '.id' => $unitTableAlias . '.owner_id'
			]);

		$this->addUnitQuery($command, $aliasTableEncase, $aliasTable, true);
	}

	private function addRelationsQuery(DbBuilder &$command)
	{
		foreach ($this->getUnitData()['relations'] as $relationKey => $relationData) {

			$aliasTable = 'relations.' . $relationKey;
			$aliasTableEncase = "`" . $aliasTable . "`";

			if ($relationData['model'] != UnitActive::class) {

				/**
				 * @var $relationModel UnitActive
				 */
				$relationModel = Unit::createNullModelByName($relationData['model']);

				$relationModel->setNewLinkType($relationData['link']);

				$attrs = $relationModel->getModelColumnAttrs($aliasTable, true);
				$relTableName = $relationModel->getTableName();

				$modelName = 'model';
				$whereCond = [];

				if ($relationModel->isDependModel()) {
					$whereCond = [
						$aliasTableEncase . '.' . $modelName . '_name' => '"' . $this->getModelNameLower() . '"',
					];

					$whereCond[$aliasTableEncase . '.' . $modelName . '_attr'] = '"' . $relationKey . '"';
				}

				if ($this->isNulOwnerModel()) {
					if ($relationModel->isDependModel()) {
						$whereCond[$aliasTableEncase . '.' . $modelName . '_id'] = '0';

						$command->orWhere('`' . $aliasTable . '.unit`.owner_id = :currentUserid
                    AND ' . $aliasTableEncase . '.id IS NOT NULL
                    ', [
							'currentUserid' => \TFL::source()->session->currentUser()->id,
						]);

						$command->andWhere($aliasTableEncase . '.id IS NULL');
						$command->group($aliasTableEncase . '.id');
					}
				} else {
					if ($relationModel->isDependModel()) {
						$relAttrId = $aliasTableEncase . '.' . $modelName . '_id';
						$currentModelId = $this->getTableName() . '.id';
					} else {
						if ($relationData['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
							$currentModelId = $this->getTableName() . '.id';
							$relAttrId = $aliasTableEncase . '.' . $this->getModelNameLower() . '_id';
						} else {
							$relAttrId = $aliasTableEncase . '.id';
							$currentModelId = $this->getTableName() . '.' . $relationKey . '_id';
						}
					}

					$whereCond[$relAttrId] = $currentModelId;
				}

				if (!empty($whereCond)) {
					$command->addSelect(implode(',', $attrs));

					$command->leftJoin($relTableName . ' AS ' . $aliasTableEncase, $whereCond);

					$relationModel->addUnitQuery($command, $aliasTableEncase, $aliasTable, true);

					$relationModel->addOwnerQuery($command, $aliasTable);
				}

				$relationModel->setDefaultLinkType();
			}
		}
	}

	/**
	 * Обработка relations link type = LINK_HAS_ONE_TO_MANY
	 * @param $row
	 */
	private function assignRelationsData(&$row)
	{
		if (!isset($row['relations']) || empty($row['relations'])) {
			return;
		}

		foreach ($this->getUnitData()['relations'] as $key => $data) {
			if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY && isset($row['relations'][$key])) {
				$newArray = [];

				$this->recurseRelationsData($newArray, $row['relations'][$key]);

				$row['relations'][$key] = $newArray;
			}
		}

	}

	/**
	 * Рекурсия для assignRelationsData
	 * @param $newArray
	 * @param $array
	 * @param null $mainIndex
	 */
	private function recurseRelationsData(&$newArray, $array, $mainIndex = null)
	{
		foreach ($array as $index => $values) {
			$issetIndex = $mainIndex ?? $index;

			if (is_array($values)) {
				$this->recurseRelationsData($newArray, $values, $issetIndex);
			} else {
				$arrayData = tString::relationStrToArray($values);

				foreach ($arrayData as $indexValue => $value) {
					if ($mainIndex) {
						if (!isset($newArray[$indexValue][$mainIndex])) {
							$newArray[$indexValue][$mainIndex] = [];
						}

						if (!isset($newArray[$indexValue][$mainIndex][$index])) {
							$newArray[$indexValue][$mainIndex][$index] = $value;
						}
					} else {
						if (!isset($newArray[$indexValue])) {
							$newArray[$indexValue] = [];
						}

						if (!isset($newArray[$indexValue][$issetIndex])) {
							$newArray[$indexValue][$issetIndex] = $value;
						}
					}
				}
			}
		}
	}
}
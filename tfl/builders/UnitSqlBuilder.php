<?php

namespace tfl\builders;

use app\models\Image;
use app\models\User;
use tfl\exceptions\TFLNotFoundModelException;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\units\UnitOption;
use tfl\utils\tDebug;
use tfl\utils\tString;

trait UnitSqlBuilder
{
    /**
     * @param array $queryData
     * @param bool $many Вывод множества значений
     * @return array
     */
    public function prepareRowData(array $queryData = [], $many = false)
    {
        //@todo Add Exception
        if (empty($queryData)) {
            return null;
        }

        if (in_array(['id', 'password', 'name'], array_keys($queryData))) {
            return null;
        }

        $tableName = $this->getTableName();

        $command = \TFL::source()->db
            ->select(implode(',', $this->getModelColumnAttrs($tableName)))
            ->from($tableName);

        foreach ($queryData as $name => $value) {
            if ($many && $name == 'id' && is_array($value)) {
                $ids = array_map(function ($id) {
                    return tString::checkNum($id);
                }, $value);
                $query = $tableName . '.' . $name . ' IN (' . implode(',', $ids) . ')';

                $command->andWhere($query);
                continue;
            }

            if (is_array($value)) {
                $query = [];
                $replaceData = [];

                if (is_string($name)) {
                    /*
                     * ['name' => $names]
                     */
                    foreach ($value as $nameValue => $oneValue) {
                        $valueName = $tableName . '.' . $name;
                        \TFL::source()->db->prepareValues($valueName, $oneValue);

//                        $query[] = "$valueName = :$name";
//                        $replaceData[$name] = $oneValue;
                        if (!is_int($oneValue)) {
                            $oneValue = "'" . $oneValue . "'";
                        }
                        $query[] = "$valueName = $oneValue";
                    }
                } else {
                    /*
                        [
                            'login|lower' => $login,
                            'email|lower' => $login,
                        ],
                     */
                    foreach ($value as $nameValue => $oneValue) {
                        $valueName = $tableName . '.' . $nameValue;
                        \TFL::source()->db->prepareValues($valueName, $oneValue, $nameValue);

//                        $query[] = "$valueName = :$nameValue";
//                        $replaceData[$nameValue] = $oneValue;
                        if (!is_int($oneValue)) {
                            $oneValue = "'" . $oneValue . "'";
                        }
                        $query[] = "$valueName = $oneValue";
                    }
                }

                $command->andWhere(implode(' OR ', $query), $replaceData);
            } else {
                $valueName = $tableName . '.' . $name;
                \TFL::source()->db->prepareValues($valueName, $value);

                $command->andWhere("$valueName = :value", [
                    'value' => $value,
                ]);
            }
        }

        if ($this instanceof UnitActive) {
            $this->addUnitQuery($command, $tableName);
            $this->addOwnerQuery($command);
            $this->addRelationsQuery($command);
        }


        if ($many) {
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
            $row = $command->find();

            if (empty($row)) {
                return null;
            }

            $this->assignRowData($row);
            $this->assignRelationsData($row);

//            tDebug::printDebug($row);

            return $row;
        }
    }

    /**
     * Подстановка значения для столбцов, елси они существуют
     * @param $attr
     * @param $selectTable
     * @param $tableName
     * @param $encase
     * @return string
     */
    private function concatAttr($attr, $selectTable, $tableName, $encase,
                                $linkType = UnitActive::LINK_HAS_ONE_TO_ONE): string
    {
        $attrValue = 'IFNULL(';

        if ($linkType == UnitActive::LINK_HAS_ONE_TO_MANY) {
            $attrValue .= 'GROUP_CONCAT("{", ' . $selectTable . '.' . $attr . ', "}")';
        } else {
            $attrValue .= $selectTable . '.' . $attr;
        }

        $attrValue .= ', "' . DbBuilder::VALUE_IF_NULL . '")';
        if ($encase) {
            $attrValue .= " AS `" . $tableName . '.' . $attr . "`";
        } else {
            $attrValue .= " AS " . $attr;
        }

        return $attrValue;
    }

    /**
     * Необходимые атрибуты для вывода в select
     *
     * @param $tableName
     * @param bool $encase
     * @return array
     */
    private function getModelColumnAttrs($tableName, bool $encase = false,
                                         $linkType = UnitActive::LINK_HAS_ONE_TO_ONE): array
    {
        $rules = $this->getUnitData()['rules'];

        $selectTable = ($encase) ? "`" . $tableName . "`" : $tableName;

        $attrs = [];

        $attrs[] = $this->concatAttr('id', $selectTable, $tableName, $encase, $linkType);

        if ($this instanceof UnitOption) {
            $attrs[] = $tableName . '.name';
        }

        foreach ($this->getUnitData()['details'] as $index => $attr) {
            //@todo Поместить в один метод для проверки
            if (isset($rules[$attr]['secretField'])) {
                continue;
            }

            $attrs[] = $this->concatAttr($attr, $selectTable, $tableName, $encase, $linkType);
        }

        foreach ($this->getUnitData()['relations'] as $attr => $data) {
            //Добавления для столбцов где UnitActive, а не точная модель
            if ($data['type'] == static::RULE_TYPE_MODEL && $data['model'] == UnitActive::class) {
                $attrs[] = $this->concatAttr($attr . '_name', $selectTable, $tableName, $encase, $linkType);
                $attrs[] = $this->concatAttr($attr . '_id', $selectTable, $tableName, $encase, $linkType);
                $attrs[] = $this->concatAttr($attr . '_attr', $selectTable, $tableName, $encase, $linkType);
            }
        }

        return $attrs;
    }

    private function addUnitQuery(DbBuilder &$command, $userTableName, $tableName = null, $encase = false,
                                  $linkType = UnitActive::LINK_HAS_ONE_TO_ONE)
    {
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
            $attrs[] = $this->concatAttr($availableAttr, $selectTable, $tableName, $encase, $linkType);
        }

        $command->addSelect(implode(',', $attrs))
            ->leftJoin($tableNameJoin, [
                $selectTable . '.model_id' => $userTableName . '.id',
                $selectTable . '.model_name' => '"' . $this->getModelNameLower() . '"'
            ]);
    }

    private function addOwnerQuery(DbBuilder &$command, $aliasTable = null,
                                   $linkType = UnitActive::LINK_HAS_ONE_TO_ONE)
    {
        $unitTableAlias = (!empty($aliasTable)) ? '`' . $aliasTable . '.unit`' : static::DB_TABLE_UNIT;
        $aliasTable = (!empty($aliasTable)) ? $aliasTable . '.owner' : 'owner';

        $aliasTableEncase = "`" . $aliasTable . "`";

        //@todo исправить, добавить в переменную
        $model = new User();

        $attrs = $model->getModelColumnAttrs($aliasTable, true, $linkType);

        $command->addSelect(implode(',', $attrs))
            ->leftJoin("model_user AS " . $aliasTableEncase, [
                $aliasTableEncase . '.id' => $unitTableAlias . '.owner_id'
            ]);

        $this->addUnitQuery($command, $aliasTableEncase, $aliasTable, true, $linkType);
    }

    private function addRelationsQuery(DbBuilder &$command)
    {
        foreach ($this->getUnitData()['relations'] as $relationKey => $relationData) {
            $modelClass = $relationData['model'];
            $aliasTable = 'relations.' . $relationKey;
            $aliasTableEncase = "`" . $aliasTable . "`";

            if ($relationData['model'] != UnitActive::class) {

                /**
                 * @var $model UnitActive
                 */
                $model = new $modelClass;
                $attrs = $model->getModelColumnAttrs($aliasTable, true, $relationData['link']);
                $relTableName = $model->getTableName();

                $command->addSelect(implode(',', $attrs));

                $modelName = 'model';

                $whereCond = [
                    $aliasTableEncase . '.' . $modelName . '_name' => '"' . $this->getModelNameLower() . '"',
                    $aliasTableEncase . '.' . $modelName . '_id' => $this->getTableName() . '.id'
                ];

                if ($relationData['model'] === Image::class) {
                    $whereCond[$aliasTableEncase . '.' . $modelName . '_attr'] = '"' . $relationKey . '"';
                }

                $command->leftJoin($relTableName . ' AS ' . $aliasTableEncase, $whereCond);

                $model->addUnitQuery($command, $aliasTableEncase, $aliasTable, true, $relationData['link']);
                $this->addOwnerQuery($command, $aliasTable, $relationData['link']);
            }
        }
    }

    /**
     * Распределение на массивы, для подстановки моделей
     *
     * @param array $rowData
     */
    private function assignRowData(array &$rowData = []): void
    {
        $newArray = [];

        foreach ($rowData as $index => $data) {
            $names = explode('.', $index);
            $this->setNewRowData($newArray, $names, $data);
            unset($rowData[$index]);
        }

        $rowData = $newArray;
    }

    /**
     * Распредление на массивы строки:
     * [owner.email] => 'test@mail.ru'
     * [relations.avatar.type] => 'cover'
     *
     * @param $newArray
     * @param $names
     * @param $value
     */
    private function setNewRowData(&$newArray, $names, $value)
    {
        if ($value == DbBuilder::VALUE_IF_NULL) {
            return;
        }

        if (!isset($names[0])) {
            return;
        }

        if (count($names) == 1) {
            $newArray[$names[0]] = $value;
            return;
        }

        if (!isset($newArray[$names[0]])) {
            $newArray[$names[0]] = [];
        }

        $newNames = $names;
        array_shift($newNames);

        $this->setNewRowData($newArray[$names[0]], $newNames, $value);
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

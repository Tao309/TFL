<?php

namespace tfl\builders;

use tfl\exceptions\TFLNotFoundModelException;
use tfl\units\UnitActive;
use tfl\units\UnitOption;
use tfl\utils\tDebug;

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
            /*
                $value:
                    $name = string
                    Array
                    (
                        [0] => core.system
                        [1] => core.seo
                        [2] => core.cms
                        [3] => design.colors
                    )
                    $name = int
                    Array
                    (
                        [login|lower] => Tao309
                        [email|lower] => Tao309
                    )
             */
            if ($many && $name == 'id' && is_array($value)) {
                $ids = array_map(function ($id) {
                    return (int)$id;
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

//        tDebug::printDebug($command->getSqlRow());

        if ($many) {
            $rows = $command->findAll();

            if (empty($rows)) {
                return [];
            }

            $rows = array_map(function ($row) {
                $this->assignRowData($row);
                return $row;
            }, $rows);

            return $rows;
        } else {
            $row = $command->find();

            if (empty($row)) {
                return null;
            }

            $this->assignRowData($row);

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
    private function concatAttr($attr, $selectTable, $tableName, $encase): string
    {
        $attrValue = 'IFNULL(';
        $attrValue .= $selectTable . '.' . $attr;
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
    private function getModelColumnAttrs($tableName, bool $encase = false): array
    {
        $rules = $this->getUnitData()['rules'];

        $selectTable = ($encase) ? "`" . $tableName . "`" : $tableName;

        $attrs = [];

        $attrs[] = $this->concatAttr('id', $selectTable, $tableName, $encase);

        if ($this instanceof UnitOption) {
            $attrs[] = $tableName . '.name';
        }

        foreach ($this->getUnitData()['details'] as $index => $attr) {
            //@todo Поместить в один метод для проверки
            if (isset($rules[$attr]['secretField'])) {
                continue;
            }

            $attrs[] = $this->concatAttr($attr, $selectTable, $tableName, $encase);
        }

        return $attrs;
    }

    private function addUnitQuery(DbBuilder &$command, $userTableName, $tableName = null, $encase = false)
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
            $attrs[] = $this->concatAttr($availableAttr, $selectTable, $tableName, $encase);
        }

        $command->addSelect(implode(',', $attrs))
            ->leftJoin($tableNameJoin,
                $selectTable . '.model_id = ' . $userTableName . '.id 
                AND ' . $selectTable . '.model_name = "' . $this->getModelNameLower() . '"');
    }

    private function addOwnerQuery(DbBuilder &$command, $aliasTable = null)
    {
        $unitTableAlias = (!empty($aliasTable)) ? '`' . $aliasTable . '.unit`' : static::DB_TABLE_UNIT;
        $aliasTable = (!empty($aliasTable)) ? $aliasTable . '.owner' : 'owner';

        $aliasTableEncase = "`" . $aliasTable . "`";

        $attrs = $this->getModelColumnAttrs($aliasTable, true);

        $command->addSelect(implode(',', $attrs))
            ->leftJoin("model_user AS " . $aliasTableEncase,
                $aliasTableEncase . ".id = " . $unitTableAlias . ".owner_id");

        $this->addUnitQuery($command, $aliasTableEncase, $aliasTable, true);
    }

    private function addRelationsQuery(DbBuilder &$command)
    {
        foreach ($this->getUnitData()['relations'] as $relationKey => $relationData) {
            $aliasTable = 'relations.' . $relationKey;
            $aliasTableEncase = "`" . $aliasTable . "`";

            /**
             * @var $model UnitActive
             */
            $model = new $relationData['model'];
            $attrs = $model->getModelColumnAttrs($aliasTable, true);
            $relTableName = $model->getTableName();

            $command->addSelect(implode(',', $attrs));

            $modelName = 'model';

            $command->leftJoin($relTableName . ' AS ' . $aliasTableEncase,
                $aliasTableEncase . '.' . $modelName . '_name = "' . $this->getModelNameLower() . '" 
                AND ' . $aliasTableEncase . '.' . $modelName . '_id = ' . $this->getTableName() . '.id'
            );

            $model->addUnitQuery($command, $aliasTableEncase, $aliasTable, true);
            $this->addOwnerQuery($command, $aliasTable);
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
     * [relations.avatar.type] => 'image'
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
}

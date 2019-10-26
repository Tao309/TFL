<?php

namespace tfl\builders;

use tfl\exceptions\TFLNotFoundModelException;
use tfl\units\UnitActive;
use tfl\units\UnitOption;

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
        }

        if ($many) {
//            print_r($command->getSqlRow());
//            echo '<hr/>';

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

        $attr = 'id';
        $attrValue = $selectTable . '.' . $attr;
        if ($encase) {
            $attrValue .= " AS `" . $tableName . '.' . $attr . "`";
        }

        $attrs[] = $attrValue;

        if ($this instanceof UnitOption) {
            $attrs[] = $tableName . '.name';
        }

        foreach ($this->getUnitData()['details'] as $index => $attr) {
            //@todo Поместить в один метод для проверки
            if (isset($rules[$attr]['secretField'])) {
                continue;
            }
            $attrValue = $selectTable . '.' . $attr;
            if ($encase) {
                $attrValue .= " AS `" . $tableName . '.' . $attr . "`";
            }
            $attrs[] = $attrValue;
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
            if ($encase) {
                $attrs[] = $selectTable . '.' . $availableAttr . ' AS `' . $tableName . '.' . $availableAttr . '`';
            } else {
                $attrs[] = $selectTable . '.' . $availableAttr;
            }
        }

        $command->addSelect(implode(',', $attrs))
            ->leftJoin($tableNameJoin,
                $selectTable . '.model_id = ' . $userTableName . '.id 
                AND ' . $selectTable . '.model_name = "' . $this->getModelNameLower() . '"');
    }

    private function addOwnerQuery(DbBuilder &$command)
    {
        $aliasTable = 'owner';
        $aliasTableEncase = "`" . $aliasTable . "`";

        $attrs = $this->getModelColumnAttrs($aliasTable, true);

        $command->addSelect(implode(',', $attrs))
            ->leftJoin("model_user AS " . $aliasTableEncase, $aliasTableEncase . ".id = " . static::DB_TABLE_UNIT . ".owner_id");


        $this->addUnitQuery($command, $aliasTableEncase, $aliasTable, true);
    }

    /**
     * Распределение на массивы, для подстановки моделей
     *
     * @param array $rowData
     */
    private function assignRowData(array &$rowData = []): void
    {
        foreach ($rowData as $index => $data) {
            $names = explode('.', $index);
            $countNames = count($names);
            if ($countNames > 1) {
                if (isset($rowData[$names[0]])) {
                    $current = $newArray = $rowData[$names[0]];
                } else {
                    $current = $newArray = [];
                }

                foreach ($names as $indexName => $name) {
                    $value = ($countNames == ($indexName + 1)) ? $data : [];

                    if (!isset($current[$name]) && $indexName > 0) {
                        $newArray = array_merge($newArray, [$name => $value]);
                        $current = $newArray;
                    }
                }

                unset($rowData[$index]);
                $rowData[$names[0]] = $newArray;
            }
        }
    }
}

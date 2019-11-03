<?php

namespace tfl\builders;

use tfl\units\UnitActive;
use tfl\units\UnitOption;
use tfl\utils\tString;

/**
 * Trait UnitSqlBuilder
 * @package tfl\builders
 *
 * @property string $linkType
 */
trait UnitSqlBuilder
{
    /**
     * Тип relation связи по умолчанию
     * @var string
     */
    protected $linkType;

    /**
     * Подстановка значения для столбцов, елси они существуют
     * @param $attr
     * @param $selectTable
     * @param $tableName
     * @param $encase
     * @return string
     */
    protected function concatAttr($attr, $selectTable, $tableName, $encase): string
    {
        $attrValue = 'IFNULL(';

        if ($this->linkType == UnitActive::LINK_HAS_ONE_TO_MANY) {
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
    protected function getModelColumnAttrs($tableName, bool $encase = false): array
    {
        $selectTable = ($encase) ? "`" . $tableName . "`" : $tableName;

        $attrs = [];

        $this->getModelColumnAttrsDetails($attrs, $selectTable, $tableName, $encase);

        $this->getModelColumnAttrsRelation($attrs, $selectTable, $tableName, $encase);

        return $attrs;
    }

    private function getModelColumnAttrsDetails(&$attrs, $selectTable, $tableName, $encase)
    {
        $rules = $this->getUnitData()['rules'];

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
    }

    private function getModelColumnAttrsRelation(&$attrs, $selectTable, $tableName, $encase)
    {
        foreach ($this->getUnitData()['relations'] as $attr => $data) {
            //Добавления для столбцов где UnitActive, а не точная модель
            if ($data['type'] == static::RULE_TYPE_MODEL && $data['model'] == UnitActive::class) {
                $attrs[] = $this->concatAttr($attr . '_name', $selectTable, $tableName, $encase);
                $attrs[] = $this->concatAttr($attr . '_id', $selectTable, $tableName, $encase);
                $attrs[] = $this->concatAttr($attr . '_attr', $selectTable, $tableName, $encase);
            }
        }
    }

    /**
     * Распределение на массивы, для подстановки моделей
     *
     * @param array $rowData
     */
    protected function assignRowData(array &$rowData = []): void
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

    protected function setNewLinkType(string $linkType = null): void
    {
        if ($linkType) {
            $this->linkType = $linkType;
        } else {
            $this->setDefaultLinkType();
        }
    }

    protected function setDefaultLinkType(): void
    {
        $this->linkType = UnitActive::LINK_HAS_ONE_TO_ONE;
    }

    protected function setQueryFromInputData(DbBuilder $command, $queryData, $many)
    {
        $tableName = $this->getTableName();

        foreach ($queryData as $name => $value) {
            if ($many && $name == 'id' && is_array($value)) {
                $ids = array_map(function ($id) {
                    return tString::encodeNum($id);
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
    }
}

<?php

namespace tfl\builders;

use tfl\utils\tDebug;

trait UnitOptionSqlBuilder
{
    /**
     * @param array $queryData
     * @param array $option Настройки для запроса
     * @return array
     */
    public function prepareRowData(array $queryData = [], $option = [])
    {
        if (empty($queryData)) {
            return null;
        }

        $many = $option['many'] ?? false;

        $tableName = $this->getTableName();

        $command = \TFL::source()->db
            ->select(implode(',', $this->getModelColumnAttrs($tableName)))
            ->from($tableName);

        $this->setQueryFromInputData($command, $queryData, $many);

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

    protected function concatAttr($attr, $selectTable, $tableName, $encase): string
    {
        return $selectTable . '.' . $attr;
    }
}
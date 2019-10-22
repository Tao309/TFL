<?php

namespace tfl\builders;

use tfl\exceptions\TFLNotFoundModelException;

trait UnitSqlBuilder
{
    /**
     * @param string $name
     * @param string|int $value
     *
     * @return array
     * @throws TFLNotFoundModelException
     */
    protected function prepareRowData($name, $value)
    {
        $tableName = $this->getTableName();

        $command = \TFL:: source()->db
            ->select(implode(',', $this->getModelColumnAttrs($tableName)))
            ->from($tableName)
            ->where("$tableName.$name = :value", [
                'value' => $value,
            ]);

        $this->addUnitQuery($command, $tableName);
        $this->addOwnerQuery($command);

//        $row = $command->getSqlRow();
        $row = $command->find();

        if (empty($row)) {
            throw new TFLNotFoundModelException("Model $this->modelName $name: #$value is not found");
        }

        $this->assignRowData($row);

        return $row;
    }

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

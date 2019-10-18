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
        $row = \TFL:: source()->db
            ->select()
            ->from($this->getTableName())
            ->where("$name = :value", [
                'value' => $value,
            ])
            ->find();

        if (empty($row)) {
            throw new TFLNotFoundModelException("Model $this->modelName $name: #$value is not found");
        }

        return $row;
    }
}

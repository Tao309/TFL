<?php

namespace tfl\collections;

use tfl\interfaces\UnitCollectionInterface;
use tfl\units\Unit;

/*
 * @todo
 * Разбивка
 * Наследование, SQL
 * Показ атрибутов
 * Зависимости в будущем, алиасы таблиц и полей
 * CollectionBuilder
 */

class UnitActiveCollection extends UnitCollection implements UnitCollectionInterface
{

    public function __construct(Unit $model)
    {
        $this->dependModel = $model;
    }

    public function setAttributes(array $attrs = []): void
    {
        // @todo Добавить везде, для показа
        array_unshift($attrs, 'id');

        $this->attributes = array_map('strtolower', $attrs);
    }

    public function setQueryOffset(): int
    {
        return 0;
    }

    public function setQueryLimit(): int
    {
        return 20;
    }

    private function getQueryRows(): array
    {
        if (!is_null($this->rows)) {
            return $this->rows;
        }

        $names = implode(',', $this->attributes);

        return $this->rows = \TFL:: source()->db
            ->select($names)
            ->from($this->dependModel->getTableName())
            ->limit($this->setQueryOffset(), $this->setQueryLimit())
            ->findAll();
    }

    public function getRows()
    {
        return $this->getQueryRows();
    }

    public function getModels()
    {
        $rows = $this->getQueryRows();

        return array_map(function ($rowData) {
            return $this->dependModel->createFinalModel($rowData);
        }, $rows);
    }
}

<?php

namespace tfl\collections;

use tfl\units\Unit;

/*
 * @todo
 * Разбивка
 * Наследование, SQL
 * Показ атрибутов
 * Зависимости в будущем, алиасы таблиц и полей
 * CollectionBuilder
 */

class UnitActiveCollection
{
    /**
     * @var $dependModel Unit
     */
    private $dependModel;
    /**
     * @var $attributes array
     */
    private $attributes;

    /**
     * @var $rows array
     */
    private $rows;

    public function __construct(Unit $model)
    {
        $this->dependModel = $model;
    }

    public function setAttributes(array $attrs = [])
    {
        // @todo Добавить везде, для показа
        array_unshift($attrs, 'id');

        $this->attributes = array_map('strtolower', $attrs);
    }

    private function getQueryRows()
    {
        if (!is_null($this->rows)) {
            return $this->rows;
        }

        $names = implode(',', $this->attributes);

        return $this->rows = \TFL:: source()->db
            ->select($names)
            ->from($this->dependModel->getTableName())
            ->findAll();
    }

    public function getModels()
    {
        $rows = $this->getQueryRows();

        return array_map(function ($rowData) {
            return $this->dependModel->createModel($rowData);
        }, $rows);
    }

    public function getRows()
    {
        return $this->getQueryRows();
    }

}

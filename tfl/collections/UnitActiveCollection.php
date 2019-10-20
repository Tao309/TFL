<?php

namespace tfl\collections;

use tfl\units\Unit;

/*
 * Разбивка
 * Наследование SQL
 * Показ атрибутов
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

    public function getAll()
    {
        $names = implode(',', $this->attributes);

        return \TFL:: source()->db
            ->select($names)
            ->from($this->dependModel->getTableName())
            ->findAll();
    }

}

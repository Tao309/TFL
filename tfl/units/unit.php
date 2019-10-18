<?php

namespace tfl\units;

use tfl\observers\UnitObserver;

/**
 * Rules:
 * * type           - тип отображаемого элемента
 * * minLimit       - минимальное необходимое значение символов, иначе не проверяется
 * * limit          - максимальное необходимое значение символов, иначе не проверяется
 * * required       - поле должно быть заполнено
 * * secretField    - поле скрывать при показе
 */


/**
 * Class Unit
 * @package tfl\units
 */
abstract class Unit
{
    use UnitObserver;

    abstract protected function unitData(): array;

    abstract protected function translatedLabels(): array;

    public function __construct()
    {

    }

    protected function isNewModel()
    {
        return !isset($this->id) || !$this->id || $this->id <= 0;
    }

    public function save(): bool
    {
        if (!$this->beforeSave()) {
            return false;
        }

        //...

        $this->afterSave();

        return true;
    }

    public function delete(): bool
    {
        if (!$this->beforeDelete()) {
            return false;
        }

        //....

        $this->afterDelete();

        return true;
    }
}

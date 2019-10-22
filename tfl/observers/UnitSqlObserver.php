<?php

namespace tfl\observers;

use tfl\utils\tString;

trait UnitSqlObserver
{
    private function saveModelAttrs(): bool
    {
        //@todo Добавить проверку атрибутов
        list($attrs, $values, $sliceValues) = $this->getAttrAndValuesForSave();

        if (empty($attrs)) {
            $this->addSaveError('attributes', 'Not found attributes');
            return false;
        }

        //@todo исправтиь на ORM
        $query = '
        UPDATE ' . $this->getTableName() . '
        SET ' . implode(',', $sliceValues) . '
        WHERE id = ' . $this->id . '
        ';

        \TFL::source()->db->update($query);

        return true;
    }

    private function getAttrAndValuesForSave(): array
    {
        $attrs = $values = $sliceValues = [];

        $rules = $this->getUnitData()['rules'];

        foreach ($this->getUnitData()['details'] as $attr) {
            if (isset($rules[$attr]['secretField'])) {
                continue;
            }

            $attrs[] = $attr = mb_strtolower($attr);
            $values[] = $value = '"' . tString::checkString($this->$attr) . '"';
            $sliceValues[] = $attr . '=' . $value;
        }

        return [$attrs, $values, $sliceValues];
    }

    protected function saveModelRelations(): bool
    {
        return true;
    }
}
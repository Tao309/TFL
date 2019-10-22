<?php

namespace tfl\observers;

use tfl\units\UnitOption;
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
        //@todo Добавить проверку на запись

        if ($this->isNewModel()) {
            $query = '
            INSERT INTO ' . $this->getTableName() . '(' . implode(',', $attrs) . ')
            VALUES (' . implode(',', $values) . ')
            ';

            \TFL::source()->db->insert($query);

            $id = \TFL::source()->db->getLastInsertId();

            $this->id = $id;
        } else {
            $query = '
            UPDATE ' . $this->getTableName() . '
            SET ' . implode(',', $sliceValues) . '
            WHERE id = ' . $this->id . '
            ';

            \TFL::source()->db->update($query);
        }

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

    protected function saveModelOwner(): bool
    {
        if ($this instanceof UnitOption) {
            return true;
        }

        $dateTime = date('Y-m-d H:i:s');

        $data = [
            'model_name' => $this->getModelNameLower(),
            'model_id' => $this->id,
            'createddatetime' => $dateTime,
            'lastchangedatetime' => $dateTime,
            'owner_id' => 1,//@todo Подстановка того кто создал
        ];

        $attrs = array_keys($data);
        $values = array_map(function ($value) {
            return (is_int($value) ? $value : '"' . $value . '"');
        }, $data);

        //@todo исправтиь на ORM
        $query = '
            INSERT INTO ' . static::DB_TABLE_UNIT . '(' . implode(',', $attrs) . ')
            VALUES (' . implode(',', $values) . ')
            ';

        \TFL::source()->db->insert($query);

        return true;
    }
    protected function saveModelRelations(): bool
    {
        if ($this instanceof UnitOption) {
            return true;
        }


        return true;
    }

    protected function deleteModel()
    {
        $query = '
        DELETE FROM ' . $this->getTableName() . '
        WHERE id = ' . $this->id . '
        ';
        \TFL::source()->db->delete($query);

        return true;
    }
    protected function deleteModelUnit()
    {
        $query = '
        DELETE FROM ' . static::DB_TABLE_UNIT . '
        WHERE model_name = "' . $this->getModelNameLower() . '" AND model_id = ' . $this->id . '
        ';
        \TFL::source()->db->delete($query);

        return true;
    }

    protected function deleteModelRelations()
    {
        return true;
    }
}
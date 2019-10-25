<?php

namespace tfl\repository;

use tfl\units\UnitOption;

trait UnitRepository
{
    /**
     * Прямое сохранение атрибутов, без прохода по моделе
     *
     * @param array $data
     * @return bool
     */
    public function directSave(array $data = []): bool
    {
        if (!$this->directSaveEnabled) {
            $this->addSaveError('directSave', 'Model can not use direct save');
            return false;
        }
        if (empty($data)) {
            $this->addSaveError('directSave', 'Saving fields are empty');
            return false;
        }

        $values = [];
        foreach ($data as $index => $value) {
            $value = (is_int($value)) ? $value : '"' . $value . '"';
            $values[] = $index . ' = ' . $value;
        }

        //@todo add good ORM
        $query = '
        UPDATE ' . $this->getTableName() . '
        SET ' . implode(',', $values) . '        
        WHERE id = ' . $this->id . '
        ';

        \TFL::source()->db->update($query);

        return true;
    }

    public function save(): bool
    {
        if (!$this->beforeSave()) {
            return false;
        }

        if (!$this->saveModelAttrs()) {
            return false;
        }

        if (!$this instanceof UnitOption) {
            if (!$this->saveModelOwner()) {
                return false;
            }

            if (!$this->saveModelRelations()) {
                return false;
            }
        }

        $this->afterSave();

        return true;
    }

    public function delete(): bool
    {
        if (!$this->beforeDelete()) {
            return false;
        }

        if (!$this->deleteModel()) {
            return false;
        }

        if (!$this->deleteModelUnit()) {
            return false;
        }

        if (!$this->deleteModelRelations()) {
            return false;
        }

        $this->afterDelete();

        return true;
    }
}
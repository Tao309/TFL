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

        \TFL::source()->db->update($this->getTableName(), $data, [
            'id' => $this->id
        ]);

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
            if (!$this->saveModelUnit()) {
                return false;
            }

            //В saveModelAttrs() проходит подстановка атрибутов
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
<?php

namespace tfl\repository;

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

        if (!$this->saveModelRelations()) {
            return false;
        }

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
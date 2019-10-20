<?php

namespace tfl\repository;

trait UnitRepository
{
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
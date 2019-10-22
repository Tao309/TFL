<?php

namespace tfl\observers;

trait UnitObserver
{
    protected function beforeSave(): bool
    {
        if (!$this->verifyAttrs()) {
            return false;
        }

        return true;
    }

    protected function afterSave(): bool
    {
        return true;
    }

    protected function beforeDelete(): bool
    {
        return true;
    }

    protected function afterDelete(): bool
    {
        return true;
    }
}
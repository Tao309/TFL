<?php

namespace tfl\observers;

use tfl\units\UnitActive;

trait UnitObserver
{
    protected function beforeFind(): void
    {
        $this->setModelName();
    }

    //@todo ввести везде
    protected function afterFind(): void
    {

    }

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
        if (!$this instanceof UnitActive) {
            $this->addSaveError('model', 'Can not delete this type model');
            return false;
        }

        return true;
    }

    protected function afterDelete(): bool
    {
        return true;
    }
}
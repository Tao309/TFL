<?php

namespace tfl\observers;

use tfl\units\UnitActive;
use tfl\utils\tAccess;

trait UnitObserver
{
    protected function beforeFind(): void
    {
        $this->setModelName();
    }

    protected function afterFind(): void
    {
	    $this->keepOldValues();
    }

    protected function beforeSave(): bool
    {
        if ($this->isNewModel()) {
            if (!tAccess::canAdd($this)) {
                $this->addSaveError('access', 'You do not have access to create');
                return false;
            }
        } else {
            if (!tAccess::canEdit($this)) {
                $this->addSaveError('access', 'You do not have access to edit');
                return false;
            }
        }

        if (!empty($this->loadDataErrors)) {
            return false;
        }

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
            $this->addDeleteError('model', 'Can not delete this type model');
            return false;
        }

        if (!tAccess::canDelete($this)) {
            $this->addDeleteError('access', 'You do not have access to delete');
            return false;
        }

        return true;
    }

    protected function afterDelete(): bool
    {
        return true;
    }
}
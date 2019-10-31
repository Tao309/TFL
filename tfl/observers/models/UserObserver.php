<?php

namespace tfl\observers\models;

trait UserObserver
{
    protected function beforeFind(): void
    {
        parent::beforeFind();
        $this->enableDirectSave();
    }
}
<?php

namespace tfl\observers\models;

trait UserObserver
{
    protected function beforeFind()
    {
        parent::beforeFind();
        $this->enableDirectSave();
    }
}
<?php

namespace tfl\observers\models;

use tfl\builders\RequestBuilder;
use tfl\handlers\ImageUploadHandler;
use tfl\units\UnitActive;
use tfl\utils\tFile;
use tfl\utils\tString;

trait ImageObserver
{
    protected function beforeFind()
    {
        parent::beforeFind();
        $this->enableDirectSave();
    }

    protected function afterFind()
    {
        parent::afterFind();
    }

    protected function beforeSave(): bool
    {
        if (empty($this->model) || (!$this->model instanceof UnitActive)) {
            $this->addSaveError('model', "Parent model is not found");
            return false;
        }

        $postData = \TFL::source()->request->getRequestValue(RequestBuilder::METHOD_POST, $this->getModelName());

        //Сохраняем для действий в afterSave
        $this->fileData = $this->filename;
        $this->attr = tString::checkString($postData['attr']);

        $this->filename = 'tempName';

        return parent::beforeSave();
    }

    protected function afterSave(): bool
    {
        //В parentModel воткнуть изображение
        if (!$this->model->hasAttribute($this->attr)) {
            $this->model->{$this->attr} = $this;
        }

        $uploader = new ImageUploadHandler($this);
        unset($this->fileData);
//        unset($this->attr);
        if (!$uploader->upload()) {
            $this->addSaveError('upload', $uploader->getErrorText());
            return false;
        }

        $this->filename = $uploader->getFileName();

        //Сделать массовый directSave
        $this->directSave([
            'filename' => $this->filename,
        ]);

        return parent::afterSave();
    }

    protected function afterDelete(): bool
    {
        foreach ([
                     self::NAME_SIZE_MINI,
                     self::NAME_SIZE_NORMAL,
                     self::NAME_SIZE_FULL,
                 ] as $nameSize) {
            tFile::removeIfExists(zROOT . WEB_PATH . '/' . $this->getImagePath($nameSize));
        }

        return parent::afterDelete();
    }
}
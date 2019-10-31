<?php

namespace tfl\observers\models;

use app\models\Image;
use tfl\builders\RequestBuilder;
use tfl\handlers\ImageUploadHandler;
use tfl\units\UnitActive;
use tfl\utils\tFile;
use tfl\utils\tString;

trait ImageObserver
{
    protected function beforeFind(): void
    {
        parent::beforeFind();
        $this->enableDirectSave();
    }

    /**
     * Сохраняется всегда только новая модель, пересохранения нет
     * @return bool
     */
    protected function beforeSave(): bool
    {
        if (!parent::beforeSave()) {
            return false;
        }

        $this->fileData = $this->filename;

        $this->filename = null;

        return true;
    }

    protected function afterSave(): bool
    {
        $uploader = new ImageUploadHandler($this);
        unset($this->fileData);
        if (!$uploader->upload()) {
            $this->addSaveError('create', $uploader->getErrorText());
            return false;
        }

        $this->filename = $uploader->getFileName();

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
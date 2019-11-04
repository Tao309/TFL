<?php

namespace tfl\observers\models;

use tfl\handlers\upload\ImageUploadHandler;
use tfl\utils\tFile;

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
        $sizeData = ImageUploadHandler::getSizeDataByModelAttr($this->model, $this->model_attr);

        $uploader = new ImageUploadHandler($this, $sizeData);
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

            if ($this->isLoaded()) {
                tFile::removeIfExists(zROOT . WEB_PATH . '/' . $this->getImagePath($nameSize));
            }
        }

        return parent::afterDelete();
    }
}
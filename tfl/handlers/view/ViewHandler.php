<?php

namespace tfl\handlers\view;

use app\models\Image;
use tfl\units\Unit;
use tfl\units\UnitActive;

/**
 * Class ViewHandler
 * @package tfl\handlers\view
 *
 * @property Unit $parentModel
 * @property UnitActive $model
 * @property string $attr
 * @property string $viewType
 */
class ViewHandler
{
    protected $parentModel;
    protected $model;
    protected $attr;
    protected $viewType;

    public function __construct($parentModel, $attr, $viewType)
    {
        $this->parentModel = $parentModel;
        $this->attr = $attr;
        $this->viewType = $viewType;


        if ($this->parentModel->hasAttribute($attr)) {
            $this->model = $parentModel->$attr;
        } else {
            $modelClassName = $this->parentModel->getUnitData()['relations'][$this->attr]['model'];
            $this->model = new $modelClassName;

            if ($modelClassName === Image::class) {
                $this->prepareImageModel();
            }
        }
    }

    /**
     * Дополнительные действия для подстановки модели \app\models\Image
     */
    private function prepareImageModel(): void
    {
        $linkType = $this->parentModel->getUnitData()['relations'][$this->attr]['link'];
        if ($linkType == UnitActive::LINK_HAS_ONE_TO_ONE) {
            $this->model = Image::TYPE_IMAGE;
        } else if ($linkType == UnitActive::LINK_HAS_ONE_TO_MANY) {
            $this->model = Image::TYPE_SCREEN;
        }
    }
}
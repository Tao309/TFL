<?php

namespace tfl\handlers\view;

use app\models\Image;
use tfl\interfaces\view\ViewHandlerInterface;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\view\View;

/**
 * Class ViewHandler
 * @package tfl\handlers\view
 *
 * @property Unit $parentModel
 * @property UnitActive $model
 * @property string $attr
 * @property string $viewType
 */
class ViewHandler implements ViewHandlerInterface
{
    protected $parentModel;
    protected $model;
    protected $attr;
    protected $viewType;

    public function __construct(Unit $parentModel, $attr, $viewType)
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
        if ($this->model->hasAttribute('type')) {
            return;
        }

        $linkType = $this->parentModel->getUnitData()['relations'][$this->attr]['link'];
        if ($linkType == UnitActive::LINK_HAS_ONE_TO_ONE) {
            $this->model->type = Image::TYPE_IMAGE;
        } else if ($linkType == UnitActive::LINK_HAS_ONE_TO_MANY) {
            $this->model->type = Image::TYPE_SCREEN;
        }
    }


    /**
     * Общий метод, который распредляется в зависимости от $viewType
     * @return string
     */
    public function renderRowField(): string
    {
        if ($this->viewType == View::TYPE_VIEW_EDIT) {
            return $this->renderEditField();
        }

        return $this->renderViewField();
    }

    public function renderViewField(): string
    {
        return 'renderViewField';
    }

    public function renderEditField(): string
    {
        return 'renderEditField';
    }
}
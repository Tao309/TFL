<?php

namespace tfl\handlers\view;

use app\models\Image;
use tfl\interfaces\view\ViewHandlerInterface;
use tfl\units\UnitActive;
use tfl\view\View;

/**
 * Class ViewHandler
 * @package tfl\handlers\view
 *
 * @property UnitActive $parentModel
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

    public function __construct($attr, $viewType, UnitActive $parentModel = null, UnitActive $model = null)
    {
        $this->attr = $attr;
        $this->viewType = $viewType;

        if ($parentModel && !$model) {
            $this->parentModel = $parentModel;

            if ($parentModel->hasAttribute($attr)) {
                $this->model = $parentModel->$attr;
            } else {
                $modelClassName = $parentModel->getUnitData()['relations'][$this->attr]['model'];
                $this->model = new $modelClassName;

                if ($modelClassName === Image::class) {
                    $this->prepareNewImageModel();
                }
            }
        }
        if ($model) {
            $this->model = $model;
        }
    }

    /**
     * Дополнительные действия для подстановки модели \app\models\Image
     * @todo перенести в image
     */
    private function prepareNewImageModel(): void
    {
        $linkType = $this->parentModel->getUnitData()['relations'][$this->attr]['link'];
        if ($linkType == UnitActive::LINK_HAS_ONE_TO_ONE) {
            $this->model->type = Image::TYPE_IMAGE;
        } else if ($linkType == UnitActive::LINK_HAS_ONE_TO_MANY) {
            $this->model->type = Image::TYPE_SCREEN;
        }

        $this->model->model_name = $this->parentModel->getModelNameLower();
        $this->model->model_id = $this->parentModel->id;
        $this->model->model_attr = $this->attr;
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
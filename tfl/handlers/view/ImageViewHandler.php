<?php

namespace tfl\handlers\view;

use app\models\Image;
use app\models\User;
use tfl\builders\DbBuilder;
use tfl\builders\RequestBuilder;
use tfl\interfaces\view\ViewHandlerInterface;
use tfl\units\UnitActive;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;
use tfl\utils\tRoute;
use tfl\view\View;

/**
 * Class ImageViewHandler
 * @package tfl\handlers\view
 *
 * @property string $modelType Тип показа модели: cover, screen
 * @property Image $model
 * @property Image[] $models
 */
class ImageViewHandler extends ViewHandler implements ViewHandlerInterface
{
    private $modelType;

    private function renderFieldHeader(): string
    {
        $t = tHtmlTags::startTag('div', [
            'class' => [
                'image-model-field',
                'type-view-' . $this->viewType,
            ]
        ]);

        $loaded = null;
        if ($this->typeLink == UnitActive::LINK_HAS_ONE_TO_ONE) {
            if ($this->model->isLoaded()) {
                $loaded = 'loaded';
            }
        }

        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'image-add-file',
                'type-' . $this->modelType,
                $loaded,
            ]
        ]);

        return $t;
    }
    private function renderFieldFooter(): string
    {
        $t = '';
        $t .= tHtmlTags::endTag();
        $t .= tHtmlTags::endTag();

        return $t;
    }
    /**
     * Загрузка изображения
     * @return string
     */
    private function renderEditFieldBody()
    {
        $t = '';
        if ($this->typeLink == UnitActive::LINK_HAS_ONE_TO_MANY) {
            $t .= tHtmlTags::startTag('div', [
                'class' => ['image-add-file', 'type-screen']
            ]);
        }

        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'file-action',
            ]
        ]);

        $htmlData = tHtmlForm::generateElementData([
	        'admin/section', 'image', 'create',
        ], RequestBuilder::METHOD_POST, [
            'class' => ['http-request-upload']
        ]);

	    $htmlData .= tHtmlForm::generateDataParams($this->model->getHiddenActionData(tRoute::SECTION_ROUTE_ADD));

        $inputFile = tHtmlTags::renderClosedTag('input', [
            'type' => 'file',
            'name' => 'Image[filename]',
        ], $htmlData);

        $t .= tHtmlTags::render('label', $inputFile, [
            'class' => 'labelFile',
            'title' => 'Upload Image',
        ]);
        $t .= tHtmlTags::endTag();

        if ($this->typeLink == UnitActive::LINK_HAS_ONE_TO_MANY) {
            $t .= tHtmlTags::endTag();
        }

        //Показ изображения
        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'image-' . $this->modelType . '-field',
            ]
        ]);

        if ($this->typeLink == UnitActive::LINK_HAS_ONE_TO_MANY) {
            foreach ($this->models as $index => $model) {
                $t .= $this->renderJustView($model);
            }
        } else {
            $t .= $this->renderJustView($this->model);
        }

        $t .= tHtmlTags::endTag();

        return $t;
    }
    private function renderOpenButton(Image $model): string
    {
        return tHTML::inputLink($model->getImageUrl(Image::NAME_SIZE_FULL), 'o', [
            'class' => ['html-icon-button', 'icon-image-view', 'tImage'],
            'title' => 'Open',
            'target' => '_blank',
        ]);
    }
    private function renderInsertButton(Image $model): string
    {
        return tHTML::inputActionButton('insert', 'v', [], [
            'class' => ['html-icon-button', 'icon-image-insert', 'insert-tag'],
            'title' => 'Insert',

            'data-tag' => 'thumb',
            'data-field' => $this->parentModel->getModelName() . '[description]',
            'data-value' => $model->getImageUrl(Image::NAME_SIZE_FULL, true),
        ]);
    }
    private function renderDeleteButton(Image $model, string $route): string
    {
        $htmlData = tHtmlForm::generateElementData([
	        'admin/section', $route, tRoute::SECTION_ROUTE_DELETE,
        ], RequestBuilder::METHOD_POST);

	    $hiddenData = $model->getHiddenActionData(tRoute::SECTION_ROUTE_DELETE);

        return tHTML::inputActionButton('delete', 'x', $htmlData, [
            'class' => ['html-icon-button', 'icon-image-delete'],
            'title' => 'Delete',
            'data-params' => tHtmlForm::generateDataParams($hiddenData, true),
        ]);
    }

    /**
     * Создаём пустую модель для вводных параметров
     */
    private function initNullImageModel()
    {
        $this->model = new Image();
        $this->model->model_name = $this->parentModel->getModelNameLower();
        $this->model->model_id = $this->parentModel->id;
        $this->model->model_attr = $this->attr;
        $this->model->type = $this->modelType;
    }

    private function renderImageViewDetails(string $imageUrl)
    {
        $t = tHtmlTags::startTag('div', [
            'class' => 'view',
        ]);
        $t .= tHtmlTags::renderClosedTag('img', [
            'src' => $imageUrl,
        ]);
        $t .= tHtmlTags::endTag();

        return $t;
    }


    /**
     * Показ только изображения
     * @return string
     */
    public function renderJustView(Image $model)
    {
        if (!$model->isLoaded()) {
            if ($this->viewType == View::TYPE_VIEW_DETAILS) {
                if ($this->parentModel instanceof User) {
                    return $this->renderImageViewDetails($this->parentModel->getDefaultUserAvatar());
                }

                return $this->renderImageViewDetails($model->getImageUrl());
            }

            if ($model->type == Image::TYPE_IMAGE) {
                return '';
            }
        }

        $t = '';

        if ($this->viewType == View::TYPE_VIEW_ADD) {
            $parentModelInputName = $this->parentModel->getModelName() . '[' . $model->model_attr . ']';
            if ($model->type == Image::TYPE_SCREEN) {
                $parentModelInputName .= '[]';
            }
            $t .= tHTML::inputHidden($parentModelInputName, $model->id);
        }

        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'one-image',
            ]
        ]);

        if ($this->viewType == View::TYPE_VIEW_EDIT || $this->viewType == View::TYPE_VIEW_ADD) {
            $t .= tHtmlTags::startTag('div', [
                'class' => 'action',
            ]);

            $route = 'image';

            if ($this->viewType == View::TYPE_VIEW_EDIT || $this->viewType == View::TYPE_VIEW_ADD) {
	            $route .= WEB_SEP . $model->id;
            }

            $t .= $this->renderOpenButton($model);

            if ($this->typeLink == UnitActive::LINK_HAS_ONE_TO_MANY) {
                $t .= $this->renderInsertButton($model);
            }

            $t .= $this->renderDeleteButton($model, $route);

            $t .= tHtmlTags::endTag();
        }

        $t .= $this->renderImageViewDetails($model->getImageUrl());

        $t .= tHtmlTags::endTag();

        return $t;
    }

	public function renderOneViewField(): string
    {
        $t = $this->renderFieldHeader();

        $t .= $this->renderJustView($this->model);

        $t .= self::renderFieldFooter();

        return $t;
    }

	public function renderManyViewFields(): string
	{
		return $this->renderOneViewField();
	}

	public function renderOneEditField(): string
    {
        $t = $this->renderFieldHeader();
        $t .= $this->renderEditFieldBody();
        $t .= $this->renderFieldFooter();

        return $t;
    }

	public function renderManyEditFields(): string
	{
		return $this->renderOneEditField();
	}

    public function prepareInputModel(): void
    {
        if ($this->typeLink == UnitActive::LINK_HAS_ONE_TO_MANY) {
            $this->modelType = Image::TYPE_SCREEN;

            $this->initNullImageModel();
        } else {
            $this->modelType = Image::TYPE_IMAGE;

            if (empty($this->model)) {
                $this->initNullImageModel();
            }
        }
    }
}
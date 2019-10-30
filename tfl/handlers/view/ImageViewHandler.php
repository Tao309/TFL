<?php

namespace tfl\handlers\view;

use app\models\Image;
use tfl\builders\RequestBuilder;
use tfl\interfaces\view\ViewHandlerInterface;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;
use tfl\view\View;

/**
 * Class ImageViewHandler
 * @package tfl\handlers\view
 *
 * @property Image $model
 */
class ImageViewHandler extends ViewHandler implements ViewHandlerInterface
{
    private function renderFieldHeader(): string
    {
        $t = tHtmlTags::startTag('div', [
            'class' => [
                'image-screen-field',
                'type-view-' . $this->viewType,
            ]
        ]);

        $loaded = null;
//        if ($this->parentModel->hasAttribute($this->attr)) {
//            $loaded = 'loaded';
//        }
        if ($this->model->isLoaded()) {
            $loaded = 'loaded';
        }

        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'image-add-file',
                'type-' . ($this->model->type ?? 'cover'),//@todo доработать
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

    private function getHiddenData(string $method): array
    {
        return [
//            'Image[model][name]' => $this->parentModel->getModelNameLower(),
//            'Image[model][id]' => $this->parentModel->id,
            'Image[model][name]' => $this->model->model_name,
            'Image[model][id]' => $this->model->model_id,
            'Image[type]' => $this->model->type,
            'Image[attr]' => $this->attr,
            'Image[id]' => $this->model->id ?? 0,
            tHtmlForm::NAME_METHOD => $method,
        ];
    }

    /**
     * Показ только изображения
     * @return string
     */
    public function renderJustView()
    {
        $t = tHtmlTags::startTag('div', [
            'class' => [
                'view',
            ]
        ]);

        if ($this->viewType == View::TYPE_VIEW_EDIT || $this->viewType == View::TYPE_VIEW_ADD) {
            $t .= tHtmlTags::startTag('div', [
                'class' => 'action',
            ]);

            $htmlData = tHtmlForm::generateElementData([
                'section',
                'image',
                'delete',
            ], RequestBuilder::METHOD_POST);

            $hiddenData = $this->getHiddenData(RequestBuilder::METHOD_DELETE);
            $t .= tHTML::inputActionButton('delete', 'x', $htmlData, [
                'class' => ['html-icon-button', 'icon-image-delete'],
                'title' => 'Delete',
                'data-params' => tHtmlForm::generateDataParams($hiddenData, true),
            ]);

            $t .= tHtmlTags::endTag();
        }

        $t .= tHtmlTags::renderClosedTag('img', [
            'src' => $this->model->getImageUrl($this->attr),
        ]);

        $t .= tHtmlTags::endTag();

        return $t;
    }

    public function renderViewField(): string
    {
        $t = $this->renderFieldHeader();

        $t .= $this->renderJustView();

        $t .= self::renderFieldFooter();

        return $t;
    }

    public function renderEditField(): string
    {
        $t = $this->renderFieldHeader();

        //Загрузка изображения
        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'file-action',
            ]
        ]);
        $htmlData = tHtmlForm::generateElementData([
            'section',
            'image',
            'upload',
        ], RequestBuilder::METHOD_POST, [
            'class' => ['http-request-upload']
        ]);
        $hiddenData = $this->getHiddenData(RequestBuilder::METHOD_POST);
        $htmlData .= tHtmlForm::generateDataParams($hiddenData);

        $inputFile = tHtmlTags::renderClosedTag('input', [
            'type' => 'file',
            'name' => 'Image[filename]',
        ], $htmlData);

        $t .= tHtmlTags::render('label', $inputFile, [
            'class' => 'labelFile',
            'title' => 'Upload Image',
        ]);
        $t .= tHtmlTags::endTag();


        //Показ изображения
        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'image-field image-' . $this->model->type . '-field',
            ]
        ]);
//        if ($this->parentModel->hasAttribute($this->attr)) {
        if ($this->model->isLoaded()) {
            $t .= $this->renderJustView();
        }
        $t .= tHtmlTags::endTag();

        $t .= self::renderFieldFooter();

        return $t;
    }
}
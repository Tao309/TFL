<?php

namespace tfl\handlers\view;

use app\models\Image;
use tfl\interfaces\view\ViewHandlerInterface;
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
    public function renderRowField(): string
    {
        $t = '';

        if ($this->viewType == View::TYPE_VIEW_EDIT) {
            $t .= $this->renderEditField();
        } else {
            $t .= $this->renderViewField();
        }

        return $t;
    }

    public function renderFieldHeader(): string
    {
        $t = tHtmlTags::startTag('div', [
            'class' => [
                'image-screen-field',
                'type-view-' . $this->viewType,
            ]
        ]);
        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'image-add-file',
                'type-' . $this->model->type,
            ]
        ]);

        return $t;
    }

    public function renderFieldFooter(): string
    {
        $t = '';
        $t .= tHtmlTags::endTag();
        $t .= tHtmlTags::endTag();

        return $t;
    }

    public function renderViewField(): string
    {
        $t = $this->renderFieldHeader();

        $t .= tHtmlTags::startTag('div', [
            'class' => [
                $this->model->type . '-field',
            ]
        ]);

        $t .= tHtmlTags::renderClosedTag('img', [
            'src' => $this->model->getImageUrl($this->attr),
        ]);

        $t .= tHtmlTags::endTag();

        $t .= self::renderFieldFooter();

        return $t;
    }

    public function renderEditField(): string
    {
        $t = $this->renderFieldHeader();

        $t .= tHtmlTags::startTag('div', [
            'class' => [
                $this->model->type . '-field',
            ]
        ]);

        $t .= '<input type="file" name=""/>';

        $t .= tHtmlTags::endTag();

        $t .= self::renderFieldFooter();

        return $t;
    }
}
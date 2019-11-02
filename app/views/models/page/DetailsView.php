<?php

namespace app\views\models\Page;

use app\models\Image;
use tfl\builders\TemplateBuilder;

class DetailsView extends TemplateBuilder
{
    public function viewTitle(): string
    {
        return 'Page Details';
    }

    public function viewData(): array
    {
        $data = [
            'cover' => [
                'type' => static::VIEW_TYPE_MODEL,
                'model' => Image::class,
            ],
            'title' => [
                'type' => static::VIEW_TYPE_TEXT,
                'limit' => 100,
            ],
            'description' => [
                'type' => static::VIEW_TYPE_TEXTAREA,
                'limit' => 1000,
                'bbTags' => true,
            ],
            'screens' => [
                'type' => static::VIEW_TYPE_MODEL,
                'model' => Image::class,
            ],
        ];

        return $data;
    }
}


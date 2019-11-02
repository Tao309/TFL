<?php

namespace app\views\models\Page;

use app\models\Image;
use tfl\builders\TemplateBuilder;

class AddView extends TemplateBuilder
{
    public function viewTitle(): string
    {
        return 'Page Add';
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
                'required' => true,
            ],
            'description' => [
                'type' => static::VIEW_TYPE_TEXTAREA,
                'limit' => 1000,
                'bbTags' => true,
                'required' => true,
            ],
            'screens' => [
                'type' => static::VIEW_TYPE_MODEL,
                'model' => Image::class,
            ],
        ];

        return $data;
    }
}


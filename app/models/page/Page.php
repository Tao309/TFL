<?php

namespace app\models;

use tfl\units\UnitActive;

/**
 * Class Page
 * @package app\models
 *
 * @property string $title
 * @property string $description
 * @property Image $cover
 * @property Image[] $screens
 */
class Page extends UnitActive
{
    public function __toString()
    {
        return $this->title;
    }

    public function unitData(): array
    {
        return [
            'details' => [
                'title',
                'description',
            ],
            'relations' => [
                'cover' => [
                    'type' => self::RULE_TYPE_MODEL,
                    'model' => Image::class,
                    'link' => static::LINK_HAS_ONE_TO_ONE,
                    'data' => [
                        [120, 120],
                        [280, 280],
                        [280, 280],
                    ]
                ],
                'screens' => [
                    'type' => self::RULE_TYPE_MODEL,
                    'model' => Image::class,
                    'link' => static::LINK_HAS_ONE_TO_MANY,
                    'data' => [
                        [120, 120],
                        [600, 600],
                        [1200, 1200],
                    ]
                ],
            ],
            'rules' => [
                'title' => [
                    'type' => static::RULE_TYPE_TEXT,
                    'minLimit' => 4,
                    'limit' => 100,
                    'required' => true,
                ],
                'description' => [
                    'type' => static::RULE_TYPE_DESCRIPTION,
                    'minLimit' => 10,
                    'limit' => 1000,
                    'required' => true,
                ],
            ],
        ];
    }


    public function translatedLabels(): array
    {
        return [
            'title' => 'Title',
            'description' => 'Description',
            'cover' => 'Cover',
            'screens' => 'Screens',
        ];
    }
}
<?php

namespace app\views\models\User;

use app\models\User;
use tfl\builders\TemplateBuilder;

class DetailsView extends TemplateBuilder
{
    public function viewTitle(): string
    {
        return 'Просмотр профиля';
    }

    public function viewData(): array
    {
        $data = [
//            'headerName0' => [
//                'type' => \Template::VIEW_TYPE_HEADER,
//                'title' => 'Аватар профиля',
//            ],
//            'avatar' => [
//                'type' => \Template::VIEW_TYPE_MODEL,
//                'name' => 'Image',
//            ],
            'headerName1' => [
                'type' => static::VIEW_TYPE_HEADER,
                'title' => 'Данные',
            ],
            'login' => [
                'type' => static::VIEW_TYPE_TEXT,
                'limit' => 20,
            ],
            'email' => [
                'type' => static::VIEW_TYPE_TEXT,
                'limit' => 50,
            ],
            'status' => [
                'type' => static::VIEW_TYPE_SELECT,
                'values' => User::getStatusList(),
                'requiredLevel' => User::STATUS_ADMIN,
            ],
        ];

        return $data;
    }
}


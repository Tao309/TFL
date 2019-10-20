<?php

namespace app\views\models\User;

use app\models\User;
use tfl\builders\TemplateBuilder;

class DetailsList extends TemplateBuilder
{
    public function viewTitle(): string
    {
        return 'Список пользователей';
    }

    public function viewData(): array
    {
        $data = [
            'login' => [
                'type' => static::VIEW_TYPE_TEXT,
                'isEditLink' => true,
            ],
            'status' => [
                'type' => static::VIEW_TYPE_TEXT,
                'values' => User::getStatusList(),
            ],
        ];

        return $data;
    }

}


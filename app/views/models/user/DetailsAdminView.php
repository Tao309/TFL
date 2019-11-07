<?php

namespace app\views\models\User;

use app\models\Image;
use app\models\Role;
use app\models\User;
use tfl\builders\TemplateBuilder;

class DetailsAdminView extends TemplateBuilder
{
    public function viewTitle(): string
    {
        return 'User View';
    }

    public function viewData(): array
    {
        $data = [
            'headerName0' => [
                'type' => static::VIEW_TYPE_HEADER,
                'title' => 'User Avatar',
            ],
            'avatar' => [
                'type' => static::VIEW_TYPE_MODEL,
                'model' => Image::class,
            ],
            'headerName1' => [
                'type' => static::VIEW_TYPE_HEADER,
                'title' => 'Data',
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
	        'role' => [
		        'type' => static::VIEW_TYPE_MODEL,
		        'model' => Role::class,
	        ],
        ];

        return $data;
    }
}


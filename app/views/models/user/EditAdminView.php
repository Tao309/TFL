<?php

namespace app\views\models\User;

use app\models\Image;
use app\models\Role;
use app\models\User;
use tfl\builders\TemplateBuilder;

class EditAdminView extends TemplateBuilder
{
    public function viewTitle(): string
    {
        return 'User Edit';
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
	            'required' => true,
            ],
            'email' => [
                'type' => static::VIEW_TYPE_TEXT,
                'limit' => 50,
	            'required' => true,
            ],
	        'password' => [
		        'type' => static::VIEW_TYPE_TEXT,
		        'limit' => 20,
		        'secretField' => true,
	        ],
            'status' => [
                'type' => static::VIEW_TYPE_SELECT,
                'values' => User::getStatusList(),
                'requiredLevel' => User::STATUS_ADMIN,
	            'required' => true,
            ],
	        'role' => [
		        'type' => static::VIEW_TYPE_MODEL,
		        'model' => Role::class,
	        ],
        ];

        return $data;
    }
}


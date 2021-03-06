<?php

namespace app\views\models\User;

use tfl\builders\TemplateBuilder;

class ListAdminView extends TemplateBuilder
{
    public function viewTitle(): string
    {
        return 'User List';
    }

    public function viewData(): array
    {
        return [
            'columns' => [
                'login',
            ],
            'perPage' => 12,
        ];
    }
}


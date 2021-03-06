<?php

namespace app\views\models\Page;

use tfl\builders\TemplateBuilder;

class ListAdminView extends TemplateBuilder
{
    public function viewTitle(): string
    {
        return 'Page List';
    }

    public function viewData(): array
    {
        return [
            'columns' => [
                'title',
            ],
            'perPage' => 12,
        ];
    }
}


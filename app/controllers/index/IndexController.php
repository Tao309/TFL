<?php

namespace app\controllers;

use tfl\builders\ControllerBuilder;
use tfl\utils\tHtmlForm;

class IndexController extends ControllerBuilder
{
    public function sectionIndex()
    {
        $loginForm = tHtmlForm::loginForm();
        $registerForm = tHtmlForm::registerForm();

        $this->addAssignVars([
            'loginForm' => $loginForm,
            'registerForm' => $registerForm,
        ]);

        return $this->render();
    }

    public function sectionList()
    {
        return $this->render();
    }
}
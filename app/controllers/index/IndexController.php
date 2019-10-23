<?php

namespace app\controllers;

use tfl\builders\ControllerBuilder;
use tfl\utils\tHtmlForm;

class IndexController extends ControllerBuilder
{
    public function sectionIndex()
    {
        return $this->render();
    }

    public function sectionList()
    {
        return $this->render();
    }
}
<?php

namespace app\controllers;

use tfl\builders\ControllerBuilder;

class IndexAdminController extends ControllerBuilder
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
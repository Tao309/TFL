<?php

namespace app\controllers;

use tfl\builders\ControllerBuilder;

class IndexController extends ControllerBuilder
{
    public function sectionIndex()
    {
        return __CLASS__ . ' - ' . __FUNCTION__ . ' - ' . __METHOD__;
    }

    public function sectionList()
    {
        return __CLASS__ . ' - ' . __FUNCTION__ . ' - ' . __METHOD__;
    }
}
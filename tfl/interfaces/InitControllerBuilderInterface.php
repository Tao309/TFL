<?php

namespace tfl\interfaces;

interface InitControllerBuilderInterface
{
    public function launch();

    public function getRouteDirection();
    public function getSectionRoute();
    public function getSectionRouteType();
}
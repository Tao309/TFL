<?php

namespace tfl\interfaces;

interface InitControllerBuilderInterface
{
    public function launch();
    public function getSectionRoute();
    public function getSectionRouteType();
}
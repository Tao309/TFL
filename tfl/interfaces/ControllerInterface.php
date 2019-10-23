<?php

namespace tfl\interfaces;

interface ControllerInterface
{
    public function addAssignVars();
    public function addComputeVars();
    public function render();

    public function redirect();
}
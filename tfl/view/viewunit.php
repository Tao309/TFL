<?php

namespace tfl\view;

use tfl\builders\TemplateBuilder;

class ViewUnit extends View
{
    public function __construct(TemplateBuilder $tplBuilder)
    {
        parent::__construct($tplBuilder);
    }
}
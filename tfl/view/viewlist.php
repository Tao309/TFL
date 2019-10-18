<?php

namespace tfl\view;

use tfl\builders\TemplateBuilder;

class ViewList extends View
{
    public function __construct(TemplateBuilder $tplBuilder)
    {
        parent::__construct($tplBuilder);
    }
}
<?php

namespace tfl\collections;

use tfl\units\Unit;

class UnitCollection
{
    /**
     * @var $dependModel Unit
     */
    protected $dependModel;
    /**
     * @var $attributes array
     */
    protected $attributes;
    /**
     * @var $rows array
     */
    protected $rows;

    protected function setQueryOffset(): int
    {
        return 0;
    }

    protected function setQueryLimit(): int
    {
        return 20;
    }
}
<?php

namespace tfl\collections;

use tfl\units\Unit;

/**
 * Class UnitCollection
 * @package tfl\collections
 *
 * @property array $rows
 * @property int $allCount
 */
class UnitCollection
{
    /**
     * @var $dependModel Unit
     */
    protected $dependModel;
    /**
     * @var $attributes array
     */
    protected $attributes = [];
    /**
     * @var $rows array
     */
    protected $rows;
    /**
     * @var $rows int
     */
    protected $allCount;
}
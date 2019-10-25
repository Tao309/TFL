<?php

namespace tfl\interfaces;

use tfl\units\UnitOption;

interface UnitOptionInterface
{
    public function getOptionTitle(): string;

    public static function getByName(string $name): UnitOption;
}

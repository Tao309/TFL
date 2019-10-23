<?php

namespace tfl\interfaces;

use tfl\units\Unit;

interface UnitInterface
{
    public function createModel(Unit $model, array $rowData);

    public function unitData(): array;

    public function translatedLabels(): array;
}
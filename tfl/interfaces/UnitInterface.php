<?php

namespace tfl\interfaces;

use tfl\units\Unit;

interface UnitInterface
{
    public function createFinalModel(Unit $model, array $rowData);

    public function unitData(): array;

    public function translatedLabels(): array;

    public function attemptLoadData();
}
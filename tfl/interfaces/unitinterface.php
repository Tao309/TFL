<?php

namespace tfl\interfaces;

interface UnitInterface
{
    public function createModel(array $rowData);

    public function unitData(): array;

    public function translatedLabels(): array;
}
<?php

namespace tfl\interfaces;

use tfl\units\Unit;

interface UnitInterface
{
    public function createFinalModel(Unit $model, array $rowData);

    public function unitData(): array;

    /**
     * Список переведённых атрибутов
     * @return array
     */
    public function translatedLabels(): array;

    /**
     * Записываем в модель данные из request запроса
     * @return mixed
     */
    public function attemptLoadData();

    public function getSeoValues(): array;
}
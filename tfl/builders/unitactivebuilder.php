<?php

namespace tfl\builders;

use tfl\units\Unit;
use tfl\units\UnitActive;

trait UnitActiveBuilder
{
    public function createModel(Unit $model, array $rowData)
    {
        /**
         * @var $model UnitActive
         */

        $model->setAttributes($model, $rowData);

        return $model;
    }

    private function setAttributes(Unit $model, array $rowData): void
    {
        $model->id = $rowData['id'];

        $rules = $model->getUnitData()['rules'];

        foreach ($model->getUnitData()['details'] as $index => $attr) {
            if (isset($rules[$attr]['secretField'])) {
                continue;
            }

            $lowAttr = mb_strtolower($attr);
            $model->$attr = $rowData[$lowAttr] ?? null;
        }
    }

}

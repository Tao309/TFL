<?php

namespace tfl\builders;

use tfl\units\UnitActive;

trait UnitActiveBuilder
{
    public function createModel(array $rowData)
    {
        /**
         * @var $model UnitActive
         */
        $modelName = self::getCurrentModel();
        $model = new $modelName;

        $model->setAttributes($rowData);

        return $model;
    }

    private function setAttributes(array $rowData): void
    {
        $rules = $this->getUnitData()['rules'];

        foreach ($this->getUnitData()['details'] as $index => $attr) {
            if (isset($rules[$attr]['secretField'])) {
                continue;
            }

            $lowAttr = mb_strtolower($attr);
            $this->$attr = $rowData[$lowAttr] ?? null;
        }
    }

}

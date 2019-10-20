<?php

namespace tfl\builders;

trait UnitActiveBuilder
{
    protected function createModel(array $rowData)
    {
        $this->setAttributes($rowData);

        return $this;
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

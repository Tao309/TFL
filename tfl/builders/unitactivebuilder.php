<?php

namespace tfl\builders;

trait UnitActiveBuilder
{
    protected function setModelName(): void
    {
        $this->modelName = self::getModelName();
    }

    protected function setModelUnitData(): void
    {
        $data = $this->unitData();
        $data['details'] = $data['details'] ?? [];
        $data['relations'] = $data['relations'] ?? [];
        $data['rules'] = $data['rules'] ?? [];

        $this->modelUnitData = $this->unitData();
    }

    protected static function getModelName(): string
    {
        $calledClass = self::getCurrentModel();
        $names = explode('\\', $calledClass);
        $modelName = end($names);

        return $modelName;
    }

    protected static function getCurrentModel(): string
    {
        return get_called_class();
    }

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

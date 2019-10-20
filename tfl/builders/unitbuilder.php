<?php

namespace tfl\builders;

trait UnitBuilder
{
    protected function setModelName(): void
    {
        $this->modelName = self::parseModelName();
        $this->modelNameLower = mb_strtolower($this->getModelName());
    }

    protected function setModelUnitData(): void
    {
        $data = $this->unitData();
        $data['details'] = $data['details'] ?? [];
        $data['relations'] = $data['relations'] ?? [];
        $data['rules'] = $data['rules'] ?? [];

        $this->modelUnitData = $this->unitData();
    }

    private static function parseModelName(): string
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
}
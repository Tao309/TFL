<?php

namespace tfl\units;

use tfl\builders\UnitBuilder;
use tfl\builders\UnitSqlBuilder;
use tfl\interfaces\UnitInterface;
use tfl\observers\UnitObserver;
use tfl\repository\UnitRepository;

/**
 * Rules:
 * * type           - тип отображаемого элемента
 * * minLimit       - минимальное необходимое значение символов, иначе не проверяется
 * * limit          - максимальное необходимое значение символов, иначе не проверяется
 * * required       - поле должно быть заполнено
 * * secretField    - поле скрывать при показе, отображать при редактировании
 * * default        - значение по умолчанию при сохранении/создании
 */


/**
 * Class Unit
 * @package tfl\units
 *
 * @property int $id
 */
abstract class Unit implements UnitInterface
{
    use UnitObserver, UnitBuilder, UnitSqlBuilder, UnitRepository;

    abstract protected function unitData(): array;

    abstract protected function translatedLabels(): array;

    const DB_MODEL_PREFIX = 'model';
    const DB_TABLE_UNIT = 'unit';

    /**
     * @var $modelName string|null
     */
    private $modelName;
    /**
     * @var $modelNameLower string|null
     */
    private $modelNameLower;
    /**
     * @var $modelName array|null
     */
    private $modelUnitData;

    public function __construct()
    {

    }

    public function __toString()
    {
        return $this->getModelName() . ' #' . $this->id;
    }

    protected function isNewModel()
    {
        return !isset($this->id) || !$this->id || $this->id <= 0;
    }

    public function getLabel($attr)
    {
        return $this->translatedLabels()[$attr] ?? 'Label not found';
    }

    /**
     * Input Model Table Name
     *
     * @return string
     */
    public function getTableName(): string
    {
        return self::DB_MODEL_PREFIX . '_' . mb_strtolower($this->getModelName());
    }

    /**
     * Input model name
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }

    /**
     * Input model name lowercase
     * @return string
     */
    public function getModelNameLower(): string
    {
        return $this->modelNameLower;
    }

    protected function getUnitData(): array
    {
        return $this->modelUnitData;
    }
}

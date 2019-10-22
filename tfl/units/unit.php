<?php

namespace tfl\units;

use tfl\builders\UnitBuilder;
use tfl\builders\UnitSqlBuilder;
use tfl\observers\UnitObserver;
use tfl\observers\UnitRulesObserver;
use tfl\observers\UnitSqlObserver;
use tfl\repository\UnitRepository;

/**
 * Class Unit
 * @package tfl\units
 *
 */
abstract class Unit
{
    use UnitObserver, UnitSqlObserver, UnitBuilder, UnitSqlBuilder, UnitRepository, UnitRulesObserver;

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
     * @var $modelUnitData array|null
     */
    private $modelUnitData;

    /**
     * Возможность прямого сохранения
     * @var bool
     */
    protected $directSaveEnabled = false;

    /**
     * Ошибки при сохранении
     * @var array
     */
    protected $saveErrors = [];

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
        return $this->translatedLabels()[$attr] ?? "Label <b>$attr</b> not found";
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

    protected function hasAttribute($attrName)
    {
        return property_exists($this, $attrName);
    }

    protected function getUnitData(): array
    {
        return $this->modelUnitData;
    }

    /**
     * Включаем возможность прямого сохранения
     */
    protected function enableDirectSave(): void
    {
        $this->directSaveEnabled = true;
    }

    protected function addSaveError(string $name, string $message): void
    {
        $this->saveErrors[$name] = $message;
    }

    public function getSaveErrors(): array
    {
        return $this->saveErrors;
    }
}

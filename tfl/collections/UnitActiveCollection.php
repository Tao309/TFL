<?php

namespace tfl\collections;

use tfl\interfaces\UnitCollectionInterface;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\utils\tDebug;

/*
 * @todo
 * Разбивка
 * Наследование, SQL
 * Показ атрибутов
 * Зависимости в будущем, алиасы таблиц и полей
 * CollectionBuilder
 */

/**
 * Class UnitActiveCollection
 * @package tfl\collections
 *
 * @property UnitActive $dependModel
 * @property string $offset
 * @property string $perPage
 * @property bool $withOwner
 * @property bool $withRelations
 *
 * @property array $rows
 */
class UnitActiveCollection extends UnitCollection implements UnitCollectionInterface
{
    private $offset;
    private $perPage;
    private $withOwner = false;
    private $withRelations = false;

    public function __construct(Unit $model)
    {
        $this->dependModel = $model;
    }

    /**
     * Делаем запрос со связью с owner моделей
     */
    public function withOwner(): void
    {
        $this->withOwner = true;
    }

    /**
     * Делаем запрос со связью с relations
     */
    public function withRelations(): void
    {
        $this->withRelations = true;
    }

    public function setPerPage($perPage): void
    {
        $this->perPage = (int)$perPage;
    }

    public function setOffset($offset): void
    {
        $this->offset = (int)$offset;
    }

    public function getQueryOffset(): int
    {
        return is_int($this->offset) && $this->offset > 0 ? $this->offset : 0;
    }

    public function getQueryLimit(): int
    {
        return is_int($this->perPage) && $this->perPage > 0 ? $this->perPage : 30;
    }

    private function getQueryRows(): array
    {
        if (!is_null($this->rows)) {
            return $this->rows;
        }

        $rows = $this->dependModel->prepareRowData(['unitcollection'], [
            'many' => true,
            'skipOwner' => !$this->withOwner,
            'skipRelations' => !$this->withRelations,
            'offset' => $this->getQueryOffset(),
            'perPage' => $this->getQueryLimit(),
        ]);

//        tDebug::printData($rows);

        return $this->rows = $rows;
    }

    public function getRows(): array
    {
        return $this->getQueryRows();
    }

    public function getModels(): array
    {
        return array_map(function ($rowData) {
            return $this->dependModel->createFinalModel($this->dependModel, $rowData, true);
        }, $this->getQueryRows());
    }
}

<?php

namespace tfl\units;

use tfl\builders\{
    UnitActiveBuilder,
    UnitSqlBuilder
};

/**
 * Class UnitActive
 * @package tfl\units
 */
abstract class UnitActive extends Unit
{
    use UnitActiveBuilder, UnitSqlBuilder;

    const DB_MODEL_PREFIX = 'model';
    const DB_TABLE_UNIT = 'unit';

    const RULE_TYPE_TEXT = 'Text';
    const RULE_TYPE_DATETIME = 'DateTime';
    const RULE_TYPE_DESCRIPTION = 'Description';
    const RULE_TYPE_INT = 'Integer';

    /**
     * @var $modelName string|null
     */
    private $modelName;

    private $modelUnitData;

    public function __construct()
    {
        parent::__construct();

        $this->setModelName();
        $this->setModelUnitData();
    }

    /**
     * Input Model Table Name
     *
     * @return string
     */
    protected function getTableName()
    {
        return self::DB_MODEL_PREFIX . '_' . mb_strtolower($this->modelName);
    }

    protected function getUnitData(): array
    {
        return $this->modelUnitData;
    }

    public static function getById(int $id)
    {
        /**
         * @var $model UnitActive
         */
        $modelName = self::getCurrentModel();
        $model = new $modelName;

        $rowData = $model->prepareRowData('id', $id);

        $model->createModel($rowData);

        return $model;
    }
}

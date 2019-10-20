<?php

namespace tfl\units;

use tfl\interfaces\UnitInterface;
use tfl\builders\{UnitActiveBuilder, UnitActiveSqlBuilder};

/**
 * Class UnitActive
 * @package tfl\units
 */
abstract class UnitActive extends Unit implements UnitInterface
{
    use UnitActiveBuilder, UnitActiveSqlBuilder;

    const RULE_TYPE_TEXT = 'Text';
    const RULE_TYPE_DATETIME = 'DateTime';
    const RULE_TYPE_DESCRIPTION = 'Description';
    const RULE_TYPE_INT = 'Integer';

    public function __construct()
    {
        parent::__construct();

        $this->setModelName();
        $this->setModelUnitData();
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

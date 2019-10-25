<?php

namespace tfl\units;

use app\models\User;
use tfl\exceptions\TFLNotFoundModelException;
use tfl\interfaces\UnitInterface;
use tfl\builders\{UnitActiveBuilder, UnitActiveSqlBuilder};

/**
 * Class UnitActive
 * @package tfl\units
 *
 * @property \DateTime $createdDateTime
 * @property \DateTime $lastChangeDateTime
 * @property User $owner
 */
abstract class UnitActive extends Unit implements UnitInterface
{
    use UnitActiveBuilder, UnitActiveSqlBuilder;

    public function __construct()
    {
        parent::__construct();

        $this->setModelUnitData();
    }

    public static function getById(int $id)
    {
        /**
         * @var $model UnitActive
         */
        $modelName = self::getCurrentModel();
        $model = new $modelName;

        $rowData = $model->prepareRowData(['id' => $id]);

        if (!$rowData) {
            throw new TFLNotFoundModelException("Model {$model->getModelName()} id: #$id is not found");
        }

        $model->createFinalModel($model, $rowData, true);

        return $model;
    }

    public function attemptLoadData()
    {
        return true;
    }
}

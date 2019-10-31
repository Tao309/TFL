<?php

namespace tfl\units;

use app\models\User;
use tfl\interfaces\UnitInterface;
use tfl\builders\{RequestBuilder, UnitActiveBuilder, UnitActiveSqlBuilder};
use tfl\utils\tDebug;
use tfl\utils\tResponse;

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

    const LINK_HAS_ONE_TO_ONE = 'oneToOne';
    const LINK_HAS_ONE_TO_MANY = 'oneToMany';
    const LINK_HAS_MANY_TO_MANY = 'manyToMany';

    protected function beforeFind(): void
    {
        parent::beforeFind();
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
            return null;
            //@todo Сделать хорошую обработку ошибки, и для getByIds
//            throw new TFLNotFoundModelException("Model {$model->getModelName()} id: #$id is not found");
        }

        return $model->createFinalModel($model, $rowData, true);
    }

    public static function getModelByIdOrCatchError(int $id)
    {
        if (!$id) {
            tResponse::modelNotFound(true);
        }

        $model = static::getById($id);
        if (!$model) {
            tResponse::modelNotFound(true);
        }

        return $model;
    }

    public function getByIds(array $ids)
    {
        /**
         * @var $model UnitActive
         */
        $modelName = self::getCurrentModel();
        $model = new $modelName;

        $rowDatas = $model->prepareRowData(['id' => $ids], true);

        $models = [];

        if (!empty($rowDatas)) {
            foreach ($rowDatas as $rowData) {
                $models[] = $model->createFinalModel($model, $rowData, true);
            }
        }

        return $models;
    }

    public function attemptLoadData()
    {
        $data = \TFL::source()->request->getRequestValue(RequestBuilder::METHOD_POST, $this->getModelName());

        if (empty($data)) {
            $this->addLoadDataError('Request data is empty');
            return false;
        }

        /**
         * @var array $data
         */
        $this->setAttrsFromRequestData($data);
        $this->setRelationsFromRequestData($data);
        $this->setAttrsFromFilesData();

        return true;
    }

    public function getSeoValues(): array
    {
        return [];
    }
}

<?php

namespace tfl\builders;

use app\models\Image;
use app\models\User;
use tfl\units\Unit;
use tfl\units\UnitActive;

trait UnitActiveBuilder
{
    private $rowDataForCreateFinalModel = [];

    public function createFinalModel(Unit $model, array $rowData, $isPrimaryModel = false, $skipRelation = false)
    {
        $this->rowDataForCreateFinalModel = $rowData;
        /**
         * @var $model UnitActive
         */
        $model->setAttributes($model);
        if (!$skipRelation) {
            $model->setRelations($model);
        }

        if ($isPrimaryModel) {
            $model->setOwner($model);
        }

        unset($this->rowDataForCreateFinalModel);

        return $model;
    }

    private function setAttributes(Unit $model): void
    {
        $rowData = $this->rowDataForCreateFinalModel;

        $model->id = $rowData['id'];
        $model->createdDateTime = $rowData['createddatetime'];
        $model->lastChangeDateTime = $rowData['lastchangedatetime'];

        $rules = $model->getUnitData()['rules'];

        foreach ($model->getUnitData()['details'] as $index => $attr) {
            if (isset($rules[$attr]['secretField'])) {
                continue;
            }

            $lowAttr = mb_strtolower($attr);
            $model->$attr = $rowData[$lowAttr] ?? null;
        }
    }

    private function setOwner(Unit $model)
    {
        $owner = new User();
        $owner->createFinalModel($owner, $this->rowDataForCreateFinalModel['owner']);

        $model->owner = $owner;
    }

    private function setRelations(Unit $model)
    {
        $rowData = $this->rowDataForCreateFinalModel;
        foreach ($model->getUnitData()['relations'] as $attr => $data) {
            if ($data['type'] == static::RULE_TYPE_MODEL && isset($data['model'])) {
                if (!isset($rowData['relations'][$attr])) {
                    $model->$attr = null;
                    continue;
                }

                /**
                 * @var UnitActive $relationModel
                 */
                $relationModel = new $data['model'];

                //Добавление зависимых моделей в модели связи
                //@todo Добавить в отдельный метод как setDependRelations
                if ($relationModel instanceof Image) {
                    $relationModel->model = $model;
                }

                $model->$attr = $relationModel->createFinalModel($relationModel,
                    $rowData['relations'][$attr], true, true);

            }
        }
    }

}

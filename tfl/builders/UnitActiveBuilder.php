<?php

namespace tfl\builders;

use app\models\User;
use tfl\units\Unit;
use tfl\units\UnitActive;

trait UnitActiveBuilder
{
    public function createModel(Unit $model, array $rowData, $isPrimaryModel = false)
    {
        /**
         * @var $model UnitActive
         */
        $model->setAttributes($model, $rowData);

        if ($isPrimaryModel) {
            $model->setOwner($model, $rowData);
        }

        return $model;
    }

    private function setAttributes(Unit $model, array $rowData): void
    {
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

    private function setOwner(Unit $model, $rowData)
    {
        $owner = new User();
        $owner->createModel($owner, $rowData['owner']);

        $model->owner = $owner;
    }

}

<?php

namespace tfl\builders;

use app\models\Image;
use app\models\User;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\utils\tString;

trait UnitActiveBuilder
{
    private $rowDataForCreateFinalModel = [];

    /**
     * Распределяем данные из request по атрибутам модели
     * @param array $request
     */
    protected function setAttrsFromRequestData(array $request): void
    {
        foreach ($this->getUnitData()['details'] as $attr) {
            if (!isset($request[$attr])) {
                continue;
            }

            $this->$attr = tString::checkString($request[$attr]);
        }
    }

    /**
     * Подставляем данные из $_FILES
     */
    protected function setAttrsFromFilesData(): void
    {
        $request = \TFL::source()->request->getRequestData(RequestBuilder::METHOD_FILES);

        if (!empty($request)) {
            foreach ($this->getUnitData()['details'] as $attr) {
                if (!isset($request[$attr])) {
                    continue;
                }

                $this->$attr = $request[$attr];
            }
        }
    }

    /**
     * Распределяем данные из request по relations модели
     * @param array $request
     */
    protected function setRelationsFromRequestData(array $request): void
    {
        foreach ($this->getUnitData()['relations'] as $attr => $data) {
            if (!isset($request[$attr])) {
                continue;
            }

            $attr_id = 'id';
            $attr_name = 'name';


            if (!isset($request[$attr][$attr_id]) || !isset($request[$attr][$attr_name])) {
                continue;
            }

            $id = tString::checkNum($request[$attr][$attr_id]);
            $name = tString::checkString($request[$attr][$attr_name]);

            /**
             * @var UnitActive $findClassName
             */
            $findClassName = 'app\models\\' . ucfirst($name);

            $this->$attr = $findClassName::getById($id);
        }
    }

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
                    $relationModel->attr = $attr;
                    $relationModel->model = $model;
                }

                $model->$attr = $relationModel->createFinalModel($relationModel,
                    $rowData['relations'][$attr], true, true);

            }
        }
    }

}

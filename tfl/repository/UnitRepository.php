<?php

namespace tfl\repository;

use tfl\units\UnitActive;
use tfl\units\UnitOption;

trait UnitRepository
{
    /**
     * Прямое сохранение атрибутов, без прохода по моделе
     *
     * @param array $data
     * @return bool
     */
    public function directSave(array $data = []): bool
    {
        if (!$this->directSaveEnabled) {
            $this->addSaveError('directSave', 'Model can not use direct save');
            return false;
        }
        if (empty($data)) {
            $this->addSaveError('directSave', 'Saving fields are empty');
            return false;
        }

        \TFL::source()->db->update($this->getTableName(), $data, [
            'id' => $this->id
        ]);

        return true;
    }

    public function save(): bool
    {
        if (!$this->beforeSave()) {
            return false;
        }

        //Тут подставляется id к текущей моделе
        if (!$this->saveModelAttrs()) {
            return false;
        }

        if (!$this instanceof UnitOption) {
            if (!$this->saveModelUnit()) {
                return false;
            }

            //В saveModelAttrs() проходит подстановка атрибутов
            if (!$this->saveModelRelations()) {
                return false;
            }
        }

        if (!$this->afterSave()) {
            return false;
        }

        return true;
    }

    public function delete(): bool
    {
        if (!$this->beforeDelete()) {
            return false;
        }

        if (!$this->deleteRelationModels()) {
            return false;
        }

        if (!$this->deleteModel()) {
            return false;
        }

        if (!$this->deleteModelUnit()) {
            return false;
        }
        $this->afterDelete();

        return true;
    }

    /**
     * Удаление только зависимых моделей
     * @return bool
     */
    private function deleteRelationModels()
    {
        $hasErrors = false;

        foreach ($this->getUnitData()['relations'] as $attr => $data) {
            if (!$this->hasAttribute($attr)) {
                continue;
            }

            if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_ONE) {
                /**
                 * @var UnitActive $model
                 */
                $model = $this->$attr;
                if (!$this->deleteRelationModel($model)) {
                    $this->addDeleteError($attr, $model->getDeleteErrors());
                    $hasErrors = true;
                    break;
                }
            } else if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
                foreach ($this->$attr as $index => $model) {
                    /**
                     * @var UnitActive $model
                     */
                    if (!$this->deleteRelationModel($model)) {
                        $this->addDeleteError($attr, $model->getDeleteErrors());
                        $hasErrors = true;
                        break;
                    }
                }
            }
        }

        return !$hasErrors;
    }

    /**
     * Удаляем дочернюю модель, если она не удаляется, выводим ошибку
     * @param UnitActive $model
     * @return bool
     */
    private function deleteRelationModel(UnitActive $model)
    {
        if ($model->isDependModel()) {
            if (!$model->delete()) {
                return false;
            }
        }

        return true;
    }
}
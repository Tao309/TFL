<?php

namespace tfl\observers;

use app\models\Image;
use app\models\User;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\units\UnitOption;
use tfl\utils\tDebug;
use tfl\utils\tString;

trait UnitSqlObserver
{
    private function saveModelAttrs(): bool
    {
        //@todo Добавить проверку атрибутов
        list($attrs, $values) = $this->getAttrAndValuesForSave();

        if (empty($attrs)) {
            $this->addSaveError('attributes', 'Not found attributes');
            return false;
        }

        if ($this->isNewModel()) {
            \TFL::source()->db->insert($this->getTableName(), array_combine($attrs, $values));

            $id = \TFL::source()->db->getLastInsertId();

            $this->id = $id;
            $this->setIsWasNewModel();
        } else {
            \TFL::source()->db->update($this->getTableName(), array_combine($attrs, $values), [
                'id' => $this->id,
            ]);
        }

        return true;
    }

    private function getAttrAndValuesForSave(): array
    {
        $attrs = $values = $sliceValues = [];

        $rules = $this->getUnitData()['rules'];

        foreach ($this->getUnitData()['details'] as $attr) {
            if (isset($rules[$attr]['secretField'])) {
                continue;
            }

            $attrs[] = $attr = mb_strtolower($attr);

            $value = $this->$attr ?? null;

            if (isset($rules[$attr]) && $rules[$attr]['type'] == static::RULE_TYPE_DESCRIPTION) {
                tString::fromTextareaToDb($value);
            }

            $values[] = $value;
        }

        foreach ($this->getUnitData()['relations'] as $attr => $data) {
            if ($data['type'] == static::RULE_TYPE_MODEL) {
                if ($data['model'] == UnitActive::class) {
                    $attr = mb_strtolower($attr);

                    $attr_id = $attr . '_id';
                    $attrs[] = $attr_id;
                    $values[] = $this->$attr_id;

                    $attr_name = $attr . '_name';
                    $attrs[] = $attr_name;
                    $values[] = $this->$attr_name;

                    $attr_attr = $attr . '_attr';
                    $attrs[] = $attr_attr;
                    $values[] = $this->$attr_attr;
                }
            }

        }

        return [$attrs, $values];
    }

    protected function saveModelUnit(): bool
    {
        $dateTime = date('Y-m-d H:i:s');

        $ownerId = User::ID_USER_SYSTEM;
        if (\TFL::source()->session->isUser()) {
            $ownerId = \TFL::source()->session->currentUser()->id;
        }

        $data = [
            'model_name' => $this->getModelNameLower(),
            'model_id' => $this->id,
            'createddatetime' => $dateTime,
            'lastchangedatetime' => $dateTime,
            'owner_id' => $ownerId,
        ];

        \TFL::source()->db->insert(static::DB_TABLE_UNIT, $data, [
            'lastchangedatetime'
        ]);

        return true;
    }

    protected function saveModelRelations(): bool
    {
        if ($this->isWasNewModel()) {
            $update = [];
            foreach ($this->getUnitDataRelations() as $attr => $data) {
                if ($data['model'] == Image::class && $this->hasAttribute($attr)) {
                    if (!isset($update[$data['model']])) {
                        $update[$data['model']] = [];
                    }

                    if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_MANY) {
                        foreach ($this->$attr as $id) {
                            $update[$data['model']][] = $id;
                        }
                    } else if ($data['link'] == UnitActive::LINK_HAS_ONE_TO_ONE) {
                        $update[$data['model']][] = $this->$attr;
                    }
                }
            }

            //Обновление только для изображений
            foreach ($update as $modelName => $ids) {
                /**
                 * @var UnitActive $model ;
                 */
                $model = new $modelName;

                \TFL::source()->db->update($model->getTableName(), [
                    'model_id' => $this->id,
                ], [
                    'id' => $ids,
                ]);
            }
        }

        return true;
    }

    protected function deleteModel()
    {
        \TFL::source()->db->delete($this->getTableName(), [
            'id' => $this->id
        ]);

        return true;
    }

    protected function deleteModelUnit()
    {
        \TFL::source()->db->delete(static::DB_TABLE_UNIT, [
            'model_name' => $this->getModelNameLower(),
            'model_id' => $this->id,
        ]);

        return true;
    }
}
<?php

namespace tfl\observers;

/**
 * Rules:
 * * type           - тип отображаемого элемента
 * * minLimit       - минимальное необходимое значение символов, иначе не проверяется
 * * limit          - максимальное необходимое значение символов, иначе не проверяется
 * * required       - поле должно быть заполнено
 * * requiredOneOf  - поле должно быть заполнено и принадлежать данному массиву
 * * secretField    - поле скрывать при показе, отображать при редактировании
 * * default        - значение по умолчанию при создании
 * * file           - данные используются через $_FILES
 * * bbTags         - использовать бб-тэги для вставки в поле
 */
trait UnitRulesObserver
{
    private $ruleAttr;
    private $ruleValueAttr;
    private $ruleData;

    protected function verifyAttrs()
    {
        $countErrors = false;

        foreach ($this->getUnitData()['details'] as $index => $attr) {
            if (!$this->checkRule($attr)) {
                $countErrors = true;
            }
        }

        if ($countErrors) {
            return false;
        }

        return true;
    }

    protected function checkRule(string $attr): bool
    {
        if (!isset($this->getUnitData()['rules'][$attr])) {
            return true;
        }

        $this->ruleAttr = $attr;
        $this->ruleValueAttr = $this->$attr ?? null;
        $this->ruleData = $this->getUnitData()['rules'][$attr];

        if (isset($this->ruleData['secretField'])) {
            return true;
        }

        foreach ($this->ruleData as $name => $value) {
            switch ($name) {
                case 'requiredOneOf':
                case 'minLimit':
                case 'limit':
                case 'required':
                case 'default':
                    $actionName = 'action' . ucfirst($name);
                    if (!$this->$actionName()) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    private function actionMinLimit(): bool
    {
        if (empty($this->ruleValueAttr)) {
            return true;
        }

        if (empty($this->ruleValueAttr) || mb_strlen($this->ruleValueAttr) < $this->ruleData['minLimit']) {
            $this->addSaveError($this->ruleAttr, "Field required <b>{$this->ruleAttr}</b> min {$this->ruleData['minLimit']} symbols");
            return false;
        }

        return true;
    }

    private function actionLimit(): bool
    {
        if (empty($this->ruleValueAttr)) {
            return true;
        }

        if (empty($this->ruleValueAttr) || mb_strlen($this->ruleValueAttr) > $this->ruleData['limit']) {
            $this->addSaveError($this->ruleAttr, "Field required <b>{$this->ruleAttr}</b> max {$this->ruleData['limit']} symbols");
            return false;
        }

        return true;
    }

    private function actionRequired(): bool
    {
        if (!$this->hasAttribute($this->ruleAttr) || empty($this->ruleValueAttr)) {
            $this->addSaveError($this->ruleAttr, "Field required <b>{$this->ruleAttr}</b>");
            return false;
        }

        return true;
    }

    private function actionRequiredOneOf(): bool
    {
        if (!$this->actionRequired()) {
            return false;
        }

        if (!in_array($this->ruleValueAttr, $this->ruleData['requiredOneOf'])) {
            $this->addSaveError($this->ruleAttr, "Field required <b>{$this->ruleAttr} 
            must be one of {implode(',', $this->ruleData['requiredOneOf'])}</b>");
            return false;
        }

        return true;
    }

    private function actionDefault(): bool
    {
        if ($this->isNewModel() && empty($this->ruleValueAttr)) {
            $this->ruleValueAttr = $this->ruleData['default'];
        }

        return true;
    }
}
<?php

namespace tfl\units;

use tfl\builders\DbBuilder;
use tfl\builders\RequestBuilder;
use tfl\builders\UnitBuilder;
use tfl\builders\UnitSqlBuilder;
use tfl\observers\UnitObserver;
use tfl\observers\UnitRulesObserver;
use tfl\observers\UnitSqlObserver;
use tfl\repository\UnitRepository;
use tfl\utils\tProtocolLoader;
use tfl\utils\tResponse;
use tfl\utils\tString;

/**
 * Class Unit
 * @package tfl\units
 *
 * @property int $id
 */
abstract class Unit
{
    use UnitObserver, UnitSqlObserver, UnitBuilder, UnitSqlBuilder, UnitRepository, UnitRulesObserver;

    const DB_MODEL_PREFIX = 'model';
    const DB_TABLE_UNIT = 'unit';

    const RULE_TYPE_MODEL = 'Model';
    const RULE_TYPE_TEXT = 'Text';
    const RULE_TYPE_DATETIME = 'DateTime';
    const RULE_TYPE_DESCRIPTION = 'Description';
    const RULE_TYPE_INT = 'Integer';

    /**
     * @var $modelName string|null
     */
    private $modelName;
    /**
     * @var $modelNameLower string|null
     */
    private $modelNameLower;
    /**
     * @var $modelUnitData array|null
     */
    private $modelUnitData;

    /**
     * Возможность прямого сохранения
     * @var bool
     */
    protected $directSaveEnabled = false;

    /**
     * Ошибки при сохранении
     * @var array
     */
    protected $saveErrors = [];
    /**
     * Ошибки при удалении
     * @var array
     */
    protected $deleteErrors = [];
    /**
     * Ошибки при заполнении модели из массива запроса
     * @var array
     */
    protected $loadDataErrors = [];

    public function __construct()
    {
        $this->beforeFind();
    }

    public function __toString()
    {
        return $this->getModelName() . ' #' . $this->id;
    }

    protected function isNewModel()
    {
        return !isset($this->id) || !$this->id || $this->id <= 0;
    }

    public function getLabel($attr)
    {
        if ($this instanceof UnitOption) {
            return $this->getOptionList()[$attr]['title'] ?? "Option Label <b>$attr</b> not found";
        }

        return $this->translatedLabels()[$attr] ?? "Label <b>$attr</b> not found";
    }

    /**
     * Input Model Table Name
     *
     * @return string
     */
    public function getTableName(): string
    {
        return self::DB_MODEL_PREFIX . '_' . mb_strtolower($this->getModelName());
    }

    /**
     * Input model name
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }

    /**
     * Input model name lowercase
     * @return string
     */
    public function getModelNameLower(): string
    {
        return $this->modelNameLower;
    }

    public function hasAttribute($attrName)
    {
        return property_exists($this, $attrName) && !empty($this->$attrName);
    }

    public function getUnitData(): array
    {
        return $this->modelUnitData;
    }

    public function getUnitDataRelation(string $attr): array
    {
        return $this->getUnitData()['relations'][$attr] ?? [];
    }

    public function getUnitDataRule(string $attr): array
    {
        return $this->getUnitData()['rules'][$attr] ?? [];
    }

    /**
     * Включаем возможность прямого сохранения через directSave()
     */
    protected function enableDirectSave(): void
    {
        $this->directSaveEnabled = true;
    }

    protected function addSaveError(string $name, string $message): void
    {
        $this->saveErrors[$name] = $message;
    }
    public function getSaveErrors(): string
    {
        return implode(PAGE_BR, $this->saveErrors);
    }

    public function getSaveErrorsElements()
    {
        return array_map(function ($key) {
            return $this->getModelName() . '[' . $key . ']';
        }, array_keys($this->saveErrors));
    }

    protected function addDeleteError(string $name, string $message): void
    {
        $this->deleteErrors[$name] = $message;
    }
    public function getDeleteErrors(): string
    {
        return implode(PAGE_BR, $this->deleteErrors);
    }

    protected function addLoadDataError(string $message)
    {
        $this->loadDataErrors[] = $message;
    }
    public function getLoadDataErrors(): string
    {
        return implode(PAGE_BR, $this->loadDataErrors);
    }

    /**
     * Добавление данных в tResponse при ответе
     * @return array
     */
    public function getResponse(): array
    {
        return [
            'id' => $this->id ?? 0,
            'name' => $this->getModelNameLower(),
            'element_id' => $this->getHtmlElementId(),
        ];
    }

    public function getHtmlElementId()
    {
        return 'model-' . $this->getModelNameLower() . '-' . ($this->id ?? 0);
    }

    /**
     * Процесс создания модели при отправки запросом
     */
    public function attemptRequestCreateModel(): void
    {
        $this->attemptRequestSaveModel(true);
    }

    /**
     * Процесс сохранения модели при отправки запросом
     */
    public function attemptRequestSaveModel($create = false): void
    {
        if ($create) {
            $method = RequestBuilder::METHOD_POST;
            $action = DbBuilder::TYPE_INSERT;
        } else {
            $method = RequestBuilder::METHOD_PUT;
            $action = ($this instanceof UnitActive) ? DbBuilder::TYPE_SAVE : DbBuilder::TYPE_UPDATE;
        }

        if (\TFL::source()->request->isAjaxRequest()) {
            if (\TFL::source()->request->checkForceMethod($method)) {
                if ($this->attemptLoadData()) {
                    if ($this->save()) {
                        tResponse::resultSuccess([tString::RESPONSE_OK, $action], true, true, $this);
                    } else {
                        tResponse::resultError($this->getSaveErrors(), true, true, $this);
                    }
                } else {
                    tResponse::resultError($this->getLoadDataErrors(), true, true, $this);
                }
            }

            tProtocolLoader::closeAccess();
        }
    }

    /**
     * Процесс удаления модели при отправки запросом
     */
    public function attemptRequestDeleteModel(): void
    {
        if (\TFL::source()->request->isAjaxRequest()) {
            if (\TFL::source()->request->checkForceMethod(RequestBuilder::METHOD_DELETE)) {
                if ($this->delete()) {
                    tResponse::resultSuccess([tString::RESPONSE_OK, DbBuilder::TYPE_DELETE], true, true, $this);
                } else {
                    tResponse::resultError($this->getDeleteErrors(), true, true, $this);
                }
            }

            tProtocolLoader::closeAccess();
        }
    }
}

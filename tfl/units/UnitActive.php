<?php

namespace tfl\units;

use app\models\Image;
use app\models\User;
use tfl\interfaces\UnitInterface;
use tfl\builders\{DbBuilder, RequestBuilder, UnitActiveBuilder, UnitActiveSqlBuilder};
use tfl\utils\tDebug;
use tfl\utils\tHtmlForm;
use tfl\utils\tResponse;

/**
 * Class UnitActive
 * @package tfl\units
 *
 * @property \DateTime $createdDateTime
 * @property \DateTime $lastChangeDateTime
 * @property User $owner
 * @property bool $nullModel нулевая модель, без значений
 * @property bool $isDependModel Зависимая модель, существует только внутри другой модели
 */
abstract class UnitActive extends Unit implements UnitInterface
{
    use UnitActiveBuilder, UnitActiveSqlBuilder;

    const LINK_HAS_ONE_TO_ONE = 'oneToOne';
    const LINK_HAS_ONE_TO_MANY = 'oneToMany';
    const LINK_HAS_MANY_TO_MANY = 'manyToMany';

    private $nullModel = false;
    protected $isDependModel = false;

    protected function beforeFind(): void
    {
        parent::beforeFind();
        $this->setModelUnitData();
    }

    public function __construct($emptyModel = false)
    {
        parent::__construct();

        if ($emptyModel) {
            $this->id = 0;
        }
    }

    public static function createNullOwnerModel()
    {
        /**
         * @var UnitActive $model
         */
        $className = static::class;

        $model = new $className;

        $model->nullModel = true;

        $rowData = $model->prepareRowData(['nullmodel' => true]);

        return $model->createFinalModel($model, $rowData, true);
    }

    public function isNulOwnerModel()
    {
        return $this->nullModel;
    }

    public function getClassName()
    {
        return static::class;
    }

    /**
     * Завивисимая лм модель. Нужно удалять её, при  удалении родительской модели
     * @return bool
     */
    public function isDependModel()
    {
        return $this->isDependModel;
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

    public static function getByIds(array $ids)
    {
        /**
         * @var $model UnitActive
         */
        $modelName = self::getCurrentModel();
        $model = new $modelName;

        $rowDatas = $model->prepareRowData(['id' => $ids], ['many' => true]);

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

        if ($this->isNewModel() && !$this->isDependModel()) {
            $this->setRelationsForNewModelFromRequestData($data);
        } else {
            $this->setRelationsFromRequestData($data);
        }

        $this->setAttrsFromFilesData();

        return true;
    }

    public function getHiddenActionData(string $type): array
    {
        $data = [];

        $modelName = $this->getModelName();

        if ($this instanceof Image) {
            $data[$modelName . '[type]'] = $this->type;
            $data[$modelName . '[model][name]'] = $this->model_name;
            $data[$modelName . '[model][id]'] = $this->model_id;
            $data[$modelName . '[model][attr]'] = $this->model_attr;
        }

        switch ($type) {
            case DbBuilder::TYPE_INSERT:
                $data[tHtmlForm::NAME_METHOD] = RequestBuilder::METHOD_POST;
                break;
            case DbBuilder::TYPE_UPDATE:
            case DbBuilder::TYPE_SAVE:
                $data[tHtmlForm::NAME_METHOD] = RequestBuilder::METHOD_PUT;
                break;
            case DbBuilder::TYPE_DELETE:
                $data[$modelName . '[id]'] = $this->id;
                $data[tHtmlForm::NAME_METHOD] = RequestBuilder::METHOD_DELETE;
                break;
            case DbBuilder::TYPE_VIEW:
                $data[tHtmlForm::NAME_METHOD] = RequestBuilder::METHOD_GET;
                break;
        }

        return $data;
    }

    public function getSeoValues(): array
    {
        return [];
    }

    public function getUrl(): string
    {
        return ROOT . 'section/' . $this->getModelNameLower() . '/' . $this->id;
    }

    public function getEditUrl(): string
    {
        return ROOT . 'admin/section/' . $this->getModelNameLower() . '/' . $this->id . '/edit';
    }

    public function getAddUrl()
    {
        return ROOT . 'admin/section/' . $this->getModelNameLower() . '/add';
    }
}

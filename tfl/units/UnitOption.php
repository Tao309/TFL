<?php

namespace tfl\units;

use tfl\builders\RequestBuilder;
use tfl\builders\TemplateBuilder;
use tfl\builders\UnitOptionSqlBuilder;
use tfl\interfaces\UnitInterface;
use tfl\interfaces\UnitOptionInterface;
use tfl\utils\tCaching;
use tfl\utils\tString;

/**
 * getOptionList see to TemplateBuilder
 */

/**
 * Class UnitOption
 * @package tfl\units
 *
 *
 * @property string $title
 * @property array $option
 *
 * @property string $name
 * @property string $content
 */
class UnitOption extends Unit implements UnitInterface, UnitOptionInterface
{
    use UnitOptionSqlBuilder;

    const NAME_CORE_SYSTEM = 'core.system';
    const NAME_CORE_SEO = 'core.seo';
    const NAME_CORE_CMS = 'core.cms';

    const NAME_DESIGN_COLORS = 'design.colors';

    private static $optionTitles = [
        self::NAME_CORE_SYSTEM => 'System',
        self::NAME_CORE_SEO => 'SEO',
        self::NAME_CORE_CMS => 'CMS',

        self::NAME_DESIGN_COLORS => 'Design Colors',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->setModelUnitData();
    }

    protected function beforeSave(): bool
    {
        $this->name = $this->title;

        $this->content = tString::serialize($this->getJustOptionsList());

        return parent::beforeSave();
    }

    /**
     * Получаем код-название настроек
     * @return string
     */
    protected function getOptionCodeName(): string
    {
        return '';
    }

    /**
     * Получаем список для отображения самих натсроек, по умолчанию
     * @return array
     */
    protected function getOptionList(): array
    {
        return [];
    }

    protected function afterSave(): bool
    {
        //Пересоздать кэш файл настроек
        tCaching::recreateUnitOptionFiles([$this]);

        return parent::afterSave();
    }

    public static function getOptionTitles()
    {
        return array_keys(self::$optionTitles);
    }

    public static function getOptionClassName($name)
    {
        if (isset(self::$optionTitles[$name])) {
            $names = array_merge(['Option'], explode('.', $name));
            $names = array_map('ucfirst', $names);

            return 'app\\models\\option\\' . implode('', $names);
        }

        die('Option <b>' . $name . '</b> Not found');
    }

    public static function getByName(string $name): UnitOption
    {
        $className = self::getOptionClassName($name);
        /**
         * @var UnitOption $model
         */
        $model = new $className;

        $rowData = $model->prepareRowData(['name' => $name]);

        return $model->createFinalModel($model, $rowData);
    }

    public static function getByNames(array $names)
    {
        /**
         * @var UnitOption $model
         */
        $modelName = self::getCurrentModel();
        $model = new $modelName;

        $rowDatas = $model->prepareRowData(['name' => $names], ['many' => true]);

        $models = [];

        if (!empty($rowDatas)) {
            foreach ($rowDatas as $index => $rowData) {
                $className = self::getOptionClassName($rowData['name']);
                /**
                 * @var UnitOption $model
                 */
                $model = new $className;
                $models[] = $model->createFinalModel($model, $rowData);
            }
        }

        return $models;
    }

    /**
     * Подстановка структуры настроек и их значений
     * Структура выводимого массива:
     * [
     *      'optionName1' => 'optionValue1',
     *      'optionName2' => 'optionValue2',
     * ]
     */
    private function getOptionData(array $data, $decode = false): array
    {
        $optionArray = [];

        $optionList = $this->getOptionList();
        foreach ($optionList as $index => $row) {
            if (isset($row['type']) && $row['type'] == TemplateBuilder::VIEW_TYPE_HEADER) {
                continue;
            }

            if (isset($data[$index])) {
                $optionArray[$index] = ($decode) ? tString::decodeValue($data[$index]) : tString::encodeValue($data[$index]);
            } else {
                $optionArray[$index] = $row['value'] ?? null;
            }

        }

        return $optionArray;
    }

    public function getOptionDataForView()
    {
        $option = $this->getOptionList();
        foreach ($option as $indexName => $data) {
            if (isset($this->option[$indexName])) {
                $option[$indexName]['value'] = $this->option[$indexName];
            }
        }

        return $option;
    }

    public function getTableName(): string
    {
        return static::DB_MODEL_PREFIX . '_option';
    }

    public function unitData(): array
    {
        return [
            'details' => [
                'content',
            ],
            'rules' => [
            ],
            'relations' => [
            ],
        ];
    }

    public function translatedLabels(): array
    {
        return [
            'name' => 'Title',
            'content' => 'Content options',
        ];
    }

    public function getSeoValues(): array
    {
        return [
            'title' => $this->getOptionTitle(),
        ];
    }

    public function getOptionTitle(): string
    {
        return self::$optionTitles[$this->getOptionCodeName()] ?? 'Title not found';
    }

    public function getOptionValue(string $attr)
    {
        return $this->option[$attr] ?? null;
    }

    public function getFileName()
    {
        return $this->getOptionCodeName();
    }

    /**
     * Массив с ключами, описанием и значениями полей для подмены в beforeSave
     * @return array
     */
    private function getJustOptionsList()
    {
        return array_map(function ($row) {
            return tString::encodeValue($row);
        }, $this->option);
    }

    public function createFinalModel(Unit $model, array $rowData)
    {
        $model->title = $this->getOptionTitle();
        $this->id = $rowData['id'];

        if (empty(trim($rowData['content']))) {
            $rowData['content'] = [];
        } else {
            $rowData['content'] = tString::unserialize($rowData['content']);
        }

        $this->option = $this->getOptionData($rowData['content'], true);

        $this->afterFind();

        return $model;
    }

    public function attemptLoadData(): bool
    {
        $loadData = \TFL::source()->request->getRequestValue(RequestBuilder::METHOD_POST, $this->getModelName());

        if (empty($loadData)) {
            $this->addLoadDataError('Request data is empty');
            return false;
        }

        $option = $this->getOptionData((array)$loadData);

        if (empty($option)) {
            $this->addLoadDataError('Loaded data is empty');
            return false;
        }

        $this->option = $option;

        return true;
    }

    public function getHiddenActionData(string $type): array
    {
        return [];
    }
}

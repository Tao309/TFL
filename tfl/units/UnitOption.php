<?php

namespace tfl\units;

use tfl\builders\RequestBuilder;
use tfl\interfaces\UnitInterface;
use tfl\interfaces\UnitOptionInterface;
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
abstract class UnitOption extends Unit implements UnitInterface, UnitOptionInterface
{
    /**
     * Получаем код-название настроек
     * @return string
     */
    abstract protected function getOptionCodeName(): string;

    /**
     * Получаем список для отображения самих натсроек, по умолчанию
     * @return array
     */
    abstract protected function getOptionList(): array;

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

        $rowData = \TFL::source()->db->select('id, content')
            ->from($this->getTableName())
            ->where('name = :name', [
                'name' => $this->getOptionCodeName(),
            ])
            ->find();

        $this->createFinalModel($this, $rowData);

        $this->setModelUnitData();
    }

    protected function beforeSave(): bool
    {
        $this->name = $this->title;

        $this->content = tString::serialize(array_map(function ($row) {
            return $row;
        }, $this->option));

        return parent::beforeSave();
    }

    protected function afterSave(): bool
    {
        //Пересоздать кэш файл настроек

        return parent::afterSave();
    }

    private static function getOptionClassName($name)
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

        return $model;
    }

    /**
     * Подстановка структуры настроек и их значений
     * @param array $rowData
     */
    private function setOptionData(array $data): void
    {
        $optionList = $this->getOptionList();
        foreach ($optionList as $index => $row) {
            if (isset($data[$index])) {
                $optionList[$index]['value'] = $data[$index];
            }
        }

        $this->option = $optionList;
    }

    public function getOptionValue(string $attr)
    {
        return $this->option[$attr]['value'] ?? null;
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
            ]
        ];
    }

    public function translatedLabels(): array
    {
        return [
            'name' => 'Title',
            'content' => 'Content options',
        ];
    }

    public function getOptionTitle(): string
    {
        return self::$optionTitles[$this->getOptionCodeName()] ?? 'Title not found';
    }

    public function createFinalModel(Unit $model, array $rowData)
    {
        $model->title = $this->getOptionTitle();
        $this->id = $rowData['id'];

        $rowData['content'] = !empty($rowData['content']) ? tString::unserialize($rowData['content']) : [];
        $this->setOptionData($rowData['content']);

        return $model;
    }

    public function attemptLoadData(): bool
    {
        $data = \TFL::source()->request->getRequestValue(RequestBuilder::METHOD_POST, $this->getModelName());

        if (empty($data)) {
            $this->addLoadDataError('Request data is empty');
            return false;
        }

        $option = [];
        foreach ($this->getOptionList() as $index => $value) {
            if (isset($data[$index])) {
                $option[$index] = tString::checkString($data[$index]);
            }
        }

        if (empty($option)) {
            $this->addLoadDataError('Loaded data is empty');
            return false;
        }

        $this->option = $option;

        return true;
    }
}

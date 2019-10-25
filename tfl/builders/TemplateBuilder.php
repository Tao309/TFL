<?php

namespace tfl\builders;

use tfl\interfaces\UnitInterface;
use tfl\view\View;
use tfl\view\ViewList;
use tfl\view\ViewUnit;

/**
 * viewData:
 * * type           Тип поля
 * * title          Название поля
 * * limit          Максимальное кол-во вводомих символов
 * * values         Выбор из массива по ключу, которым является текущее поле
 * * requiredLevel  Необходимый уровень для просмотра
 * * isLink         Поле как ссылка для просмотра
 * * isEditLink     Поле как ссылка для редактирования
 * * value          Значение по-умолчанию
 * * readonly       Только для чтения, нельзя изменять поле, значение всегда постоянно из value
 */

/**
 * Class TemplateBuilder
 * @package tfl\builders
 */
abstract class TemplateBuilder
{
    const VIEW_TYPE_HEADER = 'header';
    const VIEW_TYPE_TEXT = 'text';
    const VIEW_TYPE_TEXTAREA = 'textarea';
    const VIEW_TYPE_SELECT = 'select';
    const VIEW_TYPE_CHECKBOX = 'checkbox';
    const VIEW_TYPE_DATETIME = 'datetime';

    /**
     * Вывод названия страницы
     * @return string
     */
    abstract public function viewTitle(): string;

    /**
     * Массив настроек показа страницы
     * @return array
     */
    abstract public function viewData(): array;

    /**
     * @var string
     */
    private $view;
    /**
     * @var UnitInterface
     */
    private $dependModel;
    /**
     * @var View
     */
    private $viewModel;

    public function __construct(UnitInterface $model, string $view = View::TYPE_VIEW_DETAILS)
    {
        $this->view = $view;
        $this->dependModel = $model;

        if ($this->view == View::TYPE_VIEW_LIST) {
            $this->viewModel = new ViewList($this);
        } else {
            $this->viewModel = new ViewUnit($this);
        }
    }

    public function getDependModel(): UnitInterface
    {
        return $this->dependModel;
    }

    public function geViewType(): string
    {
        return $this->view;
    }

    public function render()
    {
        return $this->viewModel->render();
    }
}
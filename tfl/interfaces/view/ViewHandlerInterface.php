<?php

namespace tfl\interfaces\view;

interface ViewHandlerInterface
{
    /*
     * Общий метод, который распредляется в зависимости от $viewType
     * @return string
     */
    public function renderRowField(): string;

    /**
     * Отображение для просмотра
     * @return string
     */
    public function renderViewField(): string;

    /**
     * Отображение для редактирования
     * @return string
     */
    public function renderEditField(): string;

    /**
     * Верхняя часть для добавления в основной показ
     * @return string
     */
    public function renderFieldHeader(): string;

    /**
     * Нижняя часть для добавления в основной показ
     * @return string
     */
    public function renderFieldFooter(): string;
}
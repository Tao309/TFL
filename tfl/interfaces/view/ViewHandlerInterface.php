<?php

namespace tfl\interfaces\view;

interface ViewHandlerInterface
{
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
     * Подготавливаем входящую модель. Дополнительные действия для подстановки модели
     */
    public function prepareInputModel(): void;
}
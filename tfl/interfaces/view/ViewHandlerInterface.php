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
}
<?php

namespace tfl\interfaces;

interface UnitCollectionInterface
{
    /**
     * Вывод offset при формировании запроса
     */
    public function getQueryOffset(): int;

    /**
     * Вывод limit при формировании запроса
     */
    public function getQueryLimit(): int;

    /**
     * Получаем список моделей
     */
    public function getModels();

    /**
     * Получаем ассоциативный массив при запросе
     */
    public function getRows();
}
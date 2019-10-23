<?php

namespace tfl\interfaces;

interface UnitCollectionInterface
{
    /**
     * Подстановка выводящих полей
     * @param array $attrs
     */
    public function setAttributes(array $attrs = []): void;

    /**
     * Вывод offset при формировании запроса
     */
    public function setQueryOffset(): int;

    /**
     * Вывод limit при формировании запроса
     */
    public function setQueryLimit(): int;

    /**
     * Получаем список моделей
     */
    public function getModels();

    /**
     * Получаем ассоциативный массив при запросе
     */
    public function getRows();
}
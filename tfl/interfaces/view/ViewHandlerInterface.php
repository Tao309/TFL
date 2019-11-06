<?php

namespace tfl\interfaces\view;

interface ViewHandlerInterface
{
	/**
	 * Отображение для просмотра одного элемента
	 * @return string
	 */
	public function renderOneViewField(): string;

	/**
	 * Отображение для просмотра несольких элементов
	 * @return string
	 */
	public function renderManyViewFields(): string;

	/**
	 * Отображение для редактирования одного элемента
	 * @return string
	 */
	public function renderOneEditField(): string;

	/**
	 * Отображение для редактирования несольких элементов
	 * @return string
	 */
	public function renderManyEditFields(): string;

	/**
	 * Подготавливаем входящую модель. Дополнительные действия для подстановки модели
	 * @return void
	 */
	public function prepareInputModel(): void;
}
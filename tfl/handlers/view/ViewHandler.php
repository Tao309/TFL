<?php

namespace tfl\handlers\view;

use app\models\Image;
use tfl\interfaces\view\ViewHandlerInterface;
use tfl\units\UnitActive;
use tfl\view\View;

/**
 * Class ViewHandler
 * @package tfl\handlers\view
 *
 * @property UnitActive $parentModel Родительская модель
 * @property UnitActive $model Текущая модель, для неё отображаем вид
 * @property UnitActive[] $models Текущие модели, для них отображаем вид
 * @property string $attr Атрибут, по которому дочерняя модель отображается
 * @property string $viewType Тип показа: edit, details
 * @property string $typeLink Тип связи дочерней модели
 */
abstract class ViewHandler
{
	protected $parentModel;
	protected $model;
	protected $models = [];
	protected $attr;
	protected $viewType;
	protected $typeLink;

	public function __construct(UnitActive $parentModel, $attr, $viewType)
	{
		$this->attr = $attr;
		$this->viewType = $viewType;
		$this->parentModel = $parentModel;

		$relationData = $parentModel->getUnitDataRelationByAttr($this->attr);
		$this->typeLink = $relationData['link'];

		if ($parentModel->hasAttribute($attr)) {
			if ($this->typeLink == UnitActive::LINK_HAS_ONE_TO_MANY) {
				$this->models = array_reverse($parentModel->$attr);
			} else {
				$this->model = $parentModel->$attr;
			}
		}

		$this->prepareInputModel();
	}

	/**
	 * Общий метод, который распредляется в зависимости от $viewType
	 * @return string
	 */
	public function renderRowField(): string
	{
		if ($this->viewType == View::TYPE_VIEW_EDIT || $this->viewType == View::TYPE_VIEW_ADD) {
			if ($this->typeLink == UnitActive::LINK_HAS_ONE_TO_MANY) {
				return $this->renderManyEditFields();
			} else {
				return $this->renderOneEditField();
			}
		} elseif ($this->viewType == View::TYPE_VIEW_DETAILS) {
			if ($this->typeLink == UnitActive::LINK_HAS_ONE_TO_MANY) {
				return $this->renderManyViewFields();
			} else {
				return $this->renderOneViewField();
			}
		}

		return '';
	}
}
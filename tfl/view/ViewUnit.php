<?php

namespace tfl\view;

use tfl\builders\DbBuilder;
use tfl\builders\PartitionBuilder;
use tfl\builders\RequestBuilder;
use tfl\builders\TemplateBuilder;
use tfl\handlers\html\BbTags;
use tfl\units\Unit;
use tfl\units\UnitOption;
use tfl\utils\tAccess;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;
use tfl\utils\tRoute;
use tfl\utils\tString;

class ViewUnit extends View
{
	public function __construct(TemplateBuilder $tplBuilder)
	{
		parent::__construct($tplBuilder);

		$this->initViewHandlers();
	}

	protected function prepareViewLoad()
	{
		return;
	}

	protected function viewBody(): string
	{
		$t = tHtmlTags::startTag('div', [
			'class' => [
				'section-option-list',
				'type-' . $this->tplBuilder->geViewType()
			]
		]);

		$elements = tHtmlTags::startTag('div', [
			'class' => [
				'view-model-table',
				'view-edit',
			]
		]);

		foreach ($this->tplBuilder->viewData() as $attr => $data) {
			$elements .= $this->viewRow($attr, $data);
		}

		if (in_array($this->tplBuilder->geViewType(), [static::TYPE_VIEW_ADD, static::TYPE_VIEW_EDIT])) {
			$elements .= $this->viewActionRow();
			$elements .= tHtmlTags::endTag('div');

			if ($this->tplBuilder->geViewType() == static::TYPE_VIEW_ADD) {
				$data = tHtmlForm::generateRestData(tRoute::SECTION_ROUTE_ADD, $this->dependModel);
				$type = tRoute::SECTION_ROUTE_CREATE;
			} else {
				$data = tHtmlForm::generateRestData(tRoute::SECTION_ROUTE_UPDATE, $this->dependModel);
				$type = tRoute::SECTION_ROUTE_UPDATE;
			}

			$t .= tHtmlForm::simpleForm($type, $data, $elements);
		} else {
			$elements .= tHtmlTags::endTag('div');
			$t .= $elements;
		}

		$t .= tHtmlTags::endTag('div');

		return $t;
	}

	protected function viewRow(string $attr, array $data): string
	{
		if ($data['type'] == TemplateBuilder::VIEW_TYPE_MODEL) {
			$nullModel = Unit::createNullModelByName($data['model']);
			if (in_array($this->tplBuilder->geViewType(), [self::TYPE_VIEW_ADD, self::TYPE_VIEW_EDIT])) {
				if (!tAccess::canEdit($nullModel)) {
					return '';
				}
			} else {
				if (!tAccess::canView($nullModel)) {
					return '';
				}
			}
		}

		if (isset($data['secretField'])) {
			if (!tAccess::isOwner($this->dependModel)) {
				return '';
			}
		}

		$class = 'type-' . $data['type'];
		if ($data['type'] == TemplateBuilder::VIEW_TYPE_HEADER) {
			$class = 'view-row-' . $data['type'];
		}

		$t = tHtmlTags::startTag('div', [
			'class' => [
				'view-row',
				$class,
			]
		]);

		if ($data['type'] == TemplateBuilder::VIEW_TYPE_HEADER) {
			$t .= tHtmlTags::render('div', $data['title'], [
				'class' => 'view-td-title'
			]);
		} else {
			if ($this->dependModel instanceof UnitOption) {
				/**
				 * @var $this ->dependModel UnitOption
				 */
				$defaultValue = $this->dependModel->getOptionValue($attr);
			} else {
				$defaultValue = $this->dependModel->$attr ?? null;
			}

			$t .= tHtmlTags::render('div', $this->dependModel->getLabel($attr), [
				'class' => 'view-td-title'
			]);

			$t .= tHtmlTags::startTag('div', [
				'class' => 'view-td-value'
			]);


			if (in_array($this->tplBuilder->geViewType(), [self::TYPE_VIEW_ADD, self::TYPE_VIEW_EDIT])) {
				//AddView
				//EditView
				$limit = $data['limit'] ?? null;

				$inputName = $this->dependModel->getModelName() . '[' . $attr . ']';

				$options = [];
				if (isset($data['disabled'])) {
					$options['disabled'] = true;
				}
				if (isset($data['readonly'])) {
					$defaultValue = $data['value'] ?? $defaultValue;
					$options['readonly'] = true;
				}
				if (isset($data['required']) && $data['required']) {
					$options['class'] = ['element-required'];
				}

				switch ($data['type']) {
					case TemplateBuilder::VIEW_TYPE_ARRAY:
						if (!is_array($defaultValue)) {
							$defaultValue = [$defaultValue];
						}

						$values = $data['values'] ?? [];

						$t .= tHTML::editArray($inputName, $defaultValue, $values);
						break;
					case TemplateBuilder::VIEW_TYPE_TEXTAREA:
						if (isset($data['bbTags']) && $data['bbTags']) {
							//Добавляем строку с бб-тэгами
							$bbTags = new BbTags($this->dependModel, $attr);
							$t .= $bbTags->render();
						}

						$t .= tHTML::inputTextarea($inputName, tString::fromDbToTextarea($defaultValue),
							$limit, $options);
						break;
					case TemplateBuilder::VIEW_TYPE_CHECKBOX:
						$t .= tHTML::inputCheckbox($inputName, $defaultValue);
						break;
					case TemplateBuilder::VIEW_TYPE_TEXT:
						$t .= tHTML::inputText($inputName, $defaultValue, $limit, $options);
						break;
					case TemplateBuilder::VIEW_TYPE_SELECT:
						$values = $data['values'] ?? [];
						$t .= tHTML::inputSelect($inputName, $values, $defaultValue, $options);
						break;
					case TemplateBuilder::VIEW_TYPE_MODEL:
						$t .= $this->viewRelationModelRow($attr);
						break;
				}
			} else {
				//DetailsView
				switch ($data['type']) {
					case TemplateBuilder::VIEW_TYPE_ARRAY:
						if (!is_array($defaultValue)) {
							$defaultValue = [$defaultValue];
						}

						$values = $data['values'] ?? [];

						$t .= tHTML::viewArray($defaultValue, $values);
						break;
					case TemplateBuilder::VIEW_TYPE_TEXTAREA:
						$t .= BbTags::replaceTags($defaultValue);
						break;
					case TemplateBuilder::VIEW_TYPE_SELECT:
						$t .= $data['values'][$defaultValue] ?? null;
						break;
					case TemplateBuilder::VIEW_TYPE_MODEL:
						$t .= $this->viewRelationModelRow($attr);
						break;
					default:
						$t .= $defaultValue;
				}
			}

			$t .= tHtmlTags::endTag('div');
		}

		$t .= tHtmlTags::endTag('div');

		return $t;
	}

	/**
	 * Отображение кнопок действий
	 * @return string
	 */
	private function viewActionRow(): string
	{
		$t = '';

		$viewType = $this->tplBuilder->geViewType();

		if (in_array($viewType, [View::TYPE_VIEW_EDIT, View::TYPE_VIEW_ADD])) {

			$t .= tHtmlTags::startTag('div', ['class' => ['view-row', 'view-row-action']]);

			$t .= tHtmlTags::render('div', '', ['class' => 'view-td-title']);

			$t .= tHtmlTags::startTag('div', ['class' => 'view-td-action']);

			if ($viewType == View::TYPE_VIEW_ADD) {
				//AddView
				if (tAccess::canAdd($this->dependModel)) {
					$t .= tHTML::inputSubmitButton('submit', 'Add');
				}
			} else {
				//EditView
				if (tAccess::canEdit($this->dependModel)) {
					$t .= tHTML::inputSubmitButton('submit', 'Save');
				}

				if (tAccess::canDelete($this->dependModel)) {
					$htmlData = tHtmlForm::generateRestButtonData(tRoute::SECTION_ROUTE_DELETE, $this->dependModel);

					$hiddenParams = $this->dependModel->getHiddenActionData(tRoute::SECTION_ROUTE_DELETE);
					$t .= tHTML::inputActionButton('Delete', 'Delete', $htmlData, [
						'class' => [
							'html-element',
							'html-button',
							'html-button-delete',
							'size-large',
						],
						'title' => 'Delete',
						'data-params' => tHtmlForm::generateDataParams($hiddenParams, true),
					]);
				}
			}

			$t .= tHtmlTags::endTag('div');
			$t .= tHtmlTags::endTag('div');
		}

		return $t;
	}

	/**
	 * Отображение строки по relations модели
	 * @param string $attr
	 * @return string
	 */
	private function viewRelationModelRow(string $attr)
	{
		return $this->getViewHandler($attr)->renderRowField();
	}

}
<?php

namespace tfl\handlers\view;

use tfl\builders\RequestBuilder;
use tfl\interfaces\view\ViewHandlerInterface;
use tfl\units\UnitActive;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;

class UnitActiveViewHandler extends ViewHandler implements ViewHandlerInterface
{
	public function renderOneViewField(): string
	{
		if (!empty($this->model)) {
			return $this->model;
		}

		return static::VALUE_EMPTY_FIELD;
	}

	public function renderManyViewFields(): string
	{
		$t = tHtmlTags::startTag('div', ['class' => 'html-model-list']);
		$t .= tHtmlTags::startTag('div', ['class' => 'list']);

		foreach ($this->models as $model) {
			$t .= tHtmlTags::render('div',
				tHTML::inputLink($model->getAdminUrl(), (string)$model, ['class' => 'title']),
				['class' => 'element']);
		}

		$t .= tHtmlTags::endTag();
		$t .= tHtmlTags::endTag();

		return $t;
	}

	private function getSingleEditElementName()
	{
		$inputName = $this->parentModel->getModelName() . '[' . $this->attr . ']';
		if ($this->typeLink == UnitActive::LINK_HAS_ONE_TO_MANY) {
			$inputName .= '[]';
		}

		return $inputName;
	}

	private function renderHeaderSingleEditField(): string
	{
		$htmlData = tHtmlForm::generateElementData([
			'admin/section', $this->dependNullModel->getModelNameLower(), 'modalList',
		], RequestBuilder::METHOD_PUT, [
			'class' => ['http-request-button', 'html-button', 'size-large']
		]);

		$htmlData .= tHtmlForm::generateDataParams([
			$this->parentModel->getModelNameLower() => $this->parentModel->id,
			'typeLink' => $this->typeLink,
			'elementName' => $this->getSingleEditElementName(),
		]);

		$button = tHTML::inputActionButton('', '', $htmlData, ['title' => 'Choose/Add Element']);

		$t = tHtmlTags::startTag('div', ['class' => 'html-model-list']);
		$t .= tHtmlTags::render('div', $button, ['class' => 'action']);
		$t .= tHtmlTags::startTag('div', ['class' => 'list']);

		return $t;
	}

	private function renderFooterSingleEditField(): string
	{
		$t = tHtmlTags::endTag();
		$t .= tHtmlTags::endTag();

		return $t;
	}

	private function renderSingleEditField(UnitActive $model): string
	{
		$t = tHtmlTags::startTag('div', ['class' => 'element']);
		$t .= tHtmlTags::render('span', (string)$model, ['class' => 'title']);
		$t .= tHtmlTags::render('button', '', [
			'class' => ['html-icon-button', 'icon-remove', 'font-icon-tfl', 'html-remove-closest'],
			'type' => 'button',
		]);
		$inputName = $this->getSingleEditElementName();

		$t .= tHTML::inputHidden($inputName, $model->id);

		$t .= tHtmlTags::endTag();

		return $t;
	}

	public function renderOneEditField(): string
	{
		$t = $this->renderHeaderSingleEditField();
		if (!empty($this->model)) {
			$t .= $this->renderSingleEditField($this->model);
		}
		$t .= $this->renderFooterSingleEditField();

		return $t;
	}

	public function renderManyEditFields(): string
	{
		$t = $this->renderHeaderSingleEditField();
		foreach ($this->models as $model) {
			$t .= $this->renderSingleEditField($model);
		}
		$t .= $this->renderFooterSingleEditField();

		return $t;
	}

	public function prepareInputModel(): void
	{

	}
}
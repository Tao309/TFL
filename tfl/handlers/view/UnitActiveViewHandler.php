<?php

namespace tfl\handlers\view;

use tfl\builders\DbBuilder;
use tfl\builders\RequestBuilder;
use tfl\interfaces\view\ViewHandlerInterface;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;

class UnitActiveViewHandler extends ViewHandler implements ViewHandlerInterface
{

	public function renderOneViewField(): string
	{
		return 'renderOneViewField';
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

	public function renderOneEditField(): string
	{
		return 'renderOneEditField';
	}

	public function renderManyEditFields(): string
	{
		$htmlData = tHtmlForm::generateElementData([
			'admin/section', 'user', 'modalList',
		], RequestBuilder::METHOD_GET, [
			'class' => ['http-request-button', 'html-button', 'size-large']
		]);

		$htmlData .= tHtmlForm::generateDataParams();

		$button = tHTML::inputActionButton('', '', $htmlData, ['title' => 'Choose/Add Element']);

		$t = tHtmlTags::startTag('div', ['class' => 'html-model-list']);
		$t .= tHtmlTags::render('div', $button, ['class' => 'action']);
		$t .= tHtmlTags::startTag('div', ['class' => 'list']);

		foreach ($this->models as $model) {
			$t .= tHtmlTags::startTag('div', ['class' => 'element']);
			$t .= tHtmlTags::render('span', (string)$model, ['class' => 'title']);
			$t .= tHtmlTags::render('button', '', [
				'class' => ['html-icon-button', 'icon-remove', 'font-icon-tfl', 'html-remove-closest'],
				'type' => 'button',
			]);
			$t .= tHTML::inputHidden('Role[users][]', $model->id);

			$t .= tHtmlTags::endTag();
		}

		$t .= tHtmlTags::endTag();
		$t .= tHtmlTags::endTag();

		return $t;
	}

	public function prepareInputModel(): void
	{

	}
}
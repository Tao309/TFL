<?php

namespace app\controllers;

use app\models\Page;
use tfl\builders\ControllerBuilder;
use tfl\utils\tDebug;
use tfl\view\View;

class PageController extends ControllerBuilder
{
	public function sectionView($id)
	{
		$model = Page::getById($id);

		$this->appendModel($model);

		return $this->render();
	}

	public function sectionEdit($id)
	{
		$model = Page::getModelByIdOrCatchError($id);

		$this->appendModel($model);
		$this->appendTypeView(View::TYPE_VIEW_EDIT);

		return $this->render();
	}

	public function sectionSave($id)
	{
		$model = Page::getModelByIdOrCatchError($id);
		$model->attemptRequestSaveModel();
	}
}
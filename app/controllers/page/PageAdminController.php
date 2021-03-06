<?php

namespace app\controllers;

use tfl\builders\ControllerBuilder;
use tfl\units\Unit;
use tfl\utils\tDebug;

class PageAdminController extends ControllerBuilder
{
	protected $enableREST = true;

	public function sectionIndex()
	{
		return $this->render();
	}

	public function sectionAdd()
	{
		return $this->render();
	}

	public function sectionView($id)
	{
		return $this->render();
	}

	public function sectionEdit($id)
	{
		return $this->render();
	}
}
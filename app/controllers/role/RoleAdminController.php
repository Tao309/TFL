<?php

namespace app\controllers;

use tfl\builders\ControllerBuilder;

class RoleAdminController extends ControllerBuilder
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

	public function sectionDetails($id)
	{
		return $this->render();
	}

	public function sectionEdit($id)
	{
		return $this->render();
	}
}
<?php

namespace tfl\builders;

use tfl\units\Unit;
use tfl\utils\tString;

trait UnitOptionBuilder
{
	public function createFinalModel(Unit $model, array $rowData)
	{
		$model->title = $this->getOptionTitle();
		$this->id = $rowData['id'];

		if (empty(trim($rowData['content']))) {
			$rowData['content'] = [];
		} else {
			$rowData['content'] = tString::unserialize($rowData['content']);
		}

		$this->option = $this->getOptionData($rowData['content'], true);

		$this->afterFind();

		return $model;
	}
}
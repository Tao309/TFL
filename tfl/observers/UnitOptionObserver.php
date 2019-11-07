<?php

namespace tfl\observers;

use tfl\utils\tCaching;
use tfl\utils\tString;

trait UnitOptionObserver
{
	public function beforeFind(): void
	{
		parent::beforeFind();

		$this->setModelUnitData();
	}

	protected function beforeSave(): bool
	{
		$this->name = $this->title;
		$this->content = tString::serialize($this->getJustOptionsList());

		return parent::beforeSave();
	}

	protected function afterSave(): bool
	{
		//Пересоздать кэш файл настроек
		tCaching::recreateUnitOptionFiles([$this]);

		return parent::afterSave();
	}
}
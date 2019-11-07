<?php

namespace tfl\observers\models;

use tfl\utils\tCrypt;

trait UserObserver
{
	protected function beforeFind(): void
	{
		parent::beforeFind();
		$this->enableDirectSave();
	}

	protected function beforeSave(): bool
	{
		if (!empty($this->password)) {
			$this->password = tCrypt::createHashPassword($this->password);
		}

		return parent::beforeSave();
	}
}
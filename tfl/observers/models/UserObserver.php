<?php

namespace tfl\observers\models;

use app\models\User;
use tfl\auth\SessionBuilder;
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
		if (!parent::beforeSave()) {
			return false;
		}

		if (!empty(trim($this->password))) {
			$this->password = tCrypt::createHashPassword($this->password);
			//Обновляем сессию
			SessionBuilder::activateSession($this);
		}

		return true;
	}
}
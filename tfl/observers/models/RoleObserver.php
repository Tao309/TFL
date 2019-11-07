<?php

namespace tfl\observers\models;

trait RoleObserver
{
	protected function beforeDelete(): bool
	{
		if (!empty($this->users)) {
			$this->addDeleteError('users', 'Role has users');
			return false;
		}

		return parent::beforeDelete();
	}
}
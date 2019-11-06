<?php

namespace tfl\partition\models;

use app\models\User;
use tfl\builders\PartitionBuilder;

class UserPartition extends PartitionBuilder
{
	protected function getTitle()
	{
		return 'Users';
	}

	protected function getClass()
	{
		return User::class;
	}
}

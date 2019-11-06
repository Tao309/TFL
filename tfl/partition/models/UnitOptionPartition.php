<?php

namespace tfl\partition\models;

use app\models\option\OptionCoreSeo;
use tfl\builders\PartitionBuilder;

class UnitOptionPartition extends PartitionBuilder
{
	//Сделать разделение на все опции

	protected function getTitle()
	{
		return 'Core Option';
	}

	protected function getClass()
	{
		return OptionCoreSeo::class;
	}
}

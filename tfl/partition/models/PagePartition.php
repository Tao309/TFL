<?php

namespace tfl\partition\models;

use app\models\Page;
use tfl\builders\PartitionBuilder;

class PagePartition extends PartitionBuilder
{
	protected function getTitle()
	{
		return 'Pages';
	}

	protected function getClass()
	{
		return Page::class;
	}
}

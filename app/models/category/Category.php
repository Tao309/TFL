<?php

namespace app\models;

use app\models\category\CategoryItem;
use tfl\units\Unit;
use tfl\units\UnitActive;

class Category extends UnitActive
{
	public function __toString()
	{
		return $this->title;
	}

	public function unitData(): array
	{
		return [
			'details' => [
				'title',
			],
			'relations' => [
				'list' => [
					'type' => self::RULE_TYPE_MODEL,
					'model' => CategoryItem::class,
					'link' => static::LINK_HAS_ONE_TO_MANY,
				],
				'model' => [
					'type' => static::RULE_TYPE_MODEL,
					'model' => Unit::class,//По id не искать, только название
					'link' => static::LINK_HAS_ONE_TO_ONE,
				],
			],
			'rules' => [
				'title' => [
					'type' => static::RULE_TYPE_TEXT,
					'limit' => 20,
				],
			],
		];
	}

	public function translatedLabels(): array
	{
		return [
		];
	}

	public static function getModelsList()
	{
		return [
			'news',
		];
	}
}
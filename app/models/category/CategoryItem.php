<?php

namespace app\models\category;

use app\models\Category;
use tfl\units\UnitActive;

class CategoryItem extends UnitActive
{
	protected $isDependModel = true;

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
				'category' => [
					'type' => self::RULE_TYPE_MODEL,
					'model' => Category::class,
					'link' => static::LINK_HAS_ONE_TO_ONE,
				],
				'parentItem' => [
					'type' => self::RULE_TYPE_MODEL,
					'model' => CategoryItem::class,
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
}
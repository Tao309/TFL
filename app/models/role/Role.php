<?php

namespace app\models;

use tfl\observers\models\RoleObserver;
use tfl\units\UnitActive;

/**
 * Class Role
 * @package app\models
 *
 * @property string $title
 * @property array $rights
 * @property User[] $users
 */
class Role extends UnitActive
{
	use RoleObserver;

	public function __toString()
	{
		return '#' . $this->id . ' ' . $this->title;
	}

	public function unitData(): array
	{
		return [
			'details' => [
				'title',
				'rights',
			],
			'relations' => [
				'users' => [
					'type' => self::RULE_TYPE_MODEL,
					'model' => User::class,
					'link' => static::LINK_HAS_ONE_TO_MANY,
				],
			],
			'rules' => [
				'title' => [
					'type' => static::RULE_TYPE_TEXT,
					'minLimit' => 4,
					'limit' => 50,
					'required' => true,
				],
				'rights' => [
					'type' => static::RULE_TYPE_ARRAY,
					'required' => true,
				],
				'users' => [
					'required' => true,
				],
			],
		];
	}

	public function translatedLabels(): array
	{
		return [
			'title' => 'Title',
			'rights' => 'Rights',
			'users' => 'Users',
		];
	}

	/**
	 * Получаем массив прав по роли
	 * @return array
	 */
	public function getRightsArray()
	{
		return $this->rights;
	}
}
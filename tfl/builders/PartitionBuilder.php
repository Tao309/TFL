<?php

namespace tfl\builders;

use app\models\{Role,
	User,
	Page,
	option\OptionCoreCms,
	option\OptionCoreSeo,
	option\OptionCoreSystem,
	option\OptionDesignColors};

abstract class PartitionBuilder
{
	abstract protected function getTitle();

	abstract protected function getClass();

	protected function getLink()
	{
		return 'admin/section/';
	}

	public static function getPartitionList()
	{
		/*
		 * 1. Построение дерева разделов.
		 * 2. Распределение доступов.
		 * 3. Создание групп с нужными доступами? Роли.
		 */
		return [
			'models' => [
				User::class => 'Users',
				Page::class => 'Pages',
				Role::class => 'Roles',
			],
			'options' => [
				OptionCoreCms::class => 'OptionCoreCms',
				OptionCoreSeo::class => 'OptionCoreSeo',
				OptionCoreSystem::class => 'OptionCoreSystem',
				OptionDesignColors::class => 'OptionDesignColors',
			],
			'modules' => [
				'eshop' => [
					//+ Доступ в сам модуль
					'models' => [
						//app\models\eshop\Product
					],
					'options' => [],
				],
			],
		];
	}
}

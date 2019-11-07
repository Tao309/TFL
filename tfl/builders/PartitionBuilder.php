<?php

namespace tfl\builders;

use app\models\{Role,
	User,
	Page,
	option\OptionCoreCms,
	option\OptionCoreSeo,
	option\OptionCoreSystem,
	option\OptionDesignColors};
use tfl\units\Unit;
use tfl\units\UnitOption;
use tfl\utils\tAccess;
use tfl\utils\tDebug;

/**
 * Class PartitionBuilder
 * @package tfl\builders
 *
 * @property array $userData
 */
class PartitionBuilder
{
	/**
	 * Пользовательский массив прав
	 * @var array
	 */
	private $userData = [];

	public function __construct()
	{
		if ($user = \TFL::source()->session->currentUser()) {
			if ($user->hasAttribute('role')) {
				$this->userData = $user->role->getRightsArray();
			}
		}
	}

	/**
	 * @param Unit $model Модель, может быть нулевая
	 * @param null $module Искать в модулях
	 * @return bool
	 */
	public function hasAccess(Unit $model, $module = null): bool
	{
		if (tAccess::hasAccessByStatus(User::STATUS_ADMIN)) {
			return true;
		}

		$type = ($model instanceof UnitOption) ? 'options' : 'models';
		$name = $model->getClassName();

		if ($module) {
			if (isset($this->userData['modules'][$module][$type][$name])) {
				return $this->userData['modules'][$module][$type][$name] > 0 ? true : false;
			}

			return false;
		}


		return (isset($this->userData[$type][$name]) && $this->userData[$type][$name] > 0) ? true : false;
	}

	public static function getPartitionList()
	{
		/*
		 * 1. Построение дерева разделов.
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

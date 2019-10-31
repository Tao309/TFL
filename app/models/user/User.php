<?php

namespace app\models;

use tfl\observers\models\UserObserver;
use tfl\units\UnitActive;

/**
 * Class User
 * @package app\models
 *
 * @property string $login
 * @property string $email
 * @property string $status
 * @property Image $avatar
 */
class User extends UnitActive
{
    use UserObserver;

    /**
     * Статусы пользователей
     */
    const STATUS_GUEST = 0;
    const STATUS_USER = 10;
    const STATUS_SUPERUSER = 20;
    const STATUS_PUBLISHER = 30;
    const STATUS_MODERATOR = 40;
    const STATUS_ADMIN = 50;
    const STATUS_SUPERADMIN = 60;

    const ID_USER_SYSTEM = 1;
    const ID_USER_ADMIN = 2;
    const ID_USER_TAO309 = 3;

    /**
     * Человеко-понятные статусы пользователей
     */
    private static $statusNames = [
        self::STATUS_GUEST => 'Гость',
        self::STATUS_USER => 'Пользователь',
        self::STATUS_SUPERUSER => 'Супер пользователь',
        self::STATUS_PUBLISHER => 'Публикатор',
        self::STATUS_MODERATOR => 'Модератор',
        self::STATUS_ADMIN => 'Админ',
        self::STATUS_SUPERADMIN => 'Супер админ',
    ];

    public function __toString()
    {
        if ($this->isNewModel()) {
            return 'Nonamed User';
        }

        return $this->login;
    }

    public function unitData(): array
    {
        return [
            'details' => [
                'login',
                'email',
                'password',
                'status',
            ],
            'relations' => [
                'avatar' => [
                    'type' => self::RULE_TYPE_MODEL,
                    'model' => Image::class,
                    'link' => static::LINK_HAS_ONE_TO_ONE,
                    'data' => [
                        [40, 40],
                        [120, 120],
                        [280, 280],
                    ]
                ],
            ],
            'rules' => [
                'login' => [
                    'type' => static::RULE_TYPE_TEXT,
                    'minLimit' => 4,
                    'limit' => 20,
                    'required' => true,
                ],
                'email' => [
                    'type' => static::RULE_TYPE_TEXT,
                    'minLimit' => 6,
                    'limit' => 50,
                    'required' => true,
                ],
                'password' => [
                    'type' => static::RULE_TYPE_TEXT,
                    'minLimit' => 6,
                    'limit' => 20,
                    'required' => true,
                    'secretField' => true,
                ],
                'status' => [
                    'type' => static::RULE_TYPE_INT,
                    'limit' => 2,
                    'required' => true,
                    'default' => self::STATUS_USER,
                ],
            ],
        ];
    }

    public function translatedLabels(): array
    {
        $labels = [];
        $labels['login'] = 'Логин';
        $labels['email'] = 'E-mail';
        $labels['password'] = 'Пароль';
        $labels['status'] = 'Статус';
        $labels['avatar'] = 'Аватар';

        $labels['firstName'] = 'Имя';
        $labels['lastName'] = 'Фамилия';
        $labels['dateBirth'] = 'Дата рождения';

        return $labels;
    }

    /**
     * @param bool $checkAuthStatus Доступ по уровню ниже смотрящего
     * @return array
     */
    public static function getStatusList(bool $checkAuthStatus = false): array
    {
        $statusNames = self::$statusNames;
        unset($statusNames[self::STATUS_GUEST]);

        return $statusNames;
    }

}


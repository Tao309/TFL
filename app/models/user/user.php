<?php

namespace app\models;

use tfl\units\UnitActive;

/**
 * Class User
 * @package app\models
 *
 * @property string $login
 * @property string $email
 * @property string $status
 */
class User extends UnitActive
{
    //@todo Переделать в другой метод
    public function __construct()
    {
        parent::__construct();

        $this->enableDirectSave();
    }
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

    public function unitData(): array
    {
        $data = [
            'details' => [
                'login',
                'email',
                'password',
                'status',
            ],
            'relations' => [
//                'avatar' => [
//                    'type' => self::HAS_ONE,
//                    'model' => self::MODEl_NAME_IMAGE,
//                ],
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

        return $data;
    }

    public function translatedLabels(): array
    {
        $labels = [];
        $labels['login'] = 'Логин';
        $labels['email'] = 'E-mail';
        $labels['password'] = 'Пароль';
        $labels['status'] = 'Статус';

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


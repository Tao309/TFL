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

}


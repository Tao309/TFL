<?php

namespace tfl\utils;

use app\models\User;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\units\UnitOption;

class tAccess
{
    private static function isAuth()
    {
        return \TFL::source()->session->currentUser();
    }

    private static function isOwner(Unit $model): bool
    {
        if (!$user = self::isAuth()) {
            return false;
        }

        if (!$model->hasAttribute('owner')) {
            return false;
        }

        if ($model instanceof User) {
            if (!self::hasAccessByStatus(User::STATUS_MODERATOR)) {
                return false;
            }

            return $user->status > $model->status;
        }

        return self::hasAccessByStatus(User::STATUS_ADMIN) || $user->id == $model->owner->id;
    }

    private static function hasAccessByStatus($status = User::STATUS_USER): bool
    {
        if (!$user = self::isAuth()) {
            return false;
        }
        return $user->status >= $status;
    }

    public static function canAdd(UnitActive $model): bool
    {
        if (!$user = self::isAuth()) {
            return false;
        }

        if ($model instanceof User) {
            return self::hasAccessByStatus(User::STATUS_ADMIN);
        }

        return self::hasAccessByStatus(User::STATUS_PUBLISHER);
    }

    public static function canView(Unit $model): bool
    {
        return true;
    }

    public static function canEdit(Unit $model): bool
    {
        if (!$user = self::isAuth()) {
            return false;
        }

        if ($model instanceof User) {
            return self::hasAccessByStatus(User::STATUS_ADMIN);
        }

        if ($model instanceof UnitOption) {
            return self::hasAccessByStatus(User::STATUS_ADMIN);
        }

        if (!self::canView($model)) {
            return false;
        }

        if (!self::isOwner($model)) {
            return false;
        }

        return true;
    }

    public static function canDelete(Unit $model): bool
    {
        if (!$user = self::isAuth()) {
            return false;
        }

        if ($model instanceof UnitOption) {
            return false;
        }

        if (!self::canEdit($model)) {
            return false;
        }

        if ($model instanceof User) {
            return self::hasAccessByStatus(User::STATUS_SUPERADMIN);
        }

        return true;
    }
}
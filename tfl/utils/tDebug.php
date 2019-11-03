<?php

namespace tfl\utils;

class tDebug
{
    /**
     * Вывод данных для показа
     * @param $data
     */
    public static function printData($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        exit;
    }

    /**
     * Debug начинается
     */
    public static function startDebug()
    {
        define('DEBUG', true);
    }

    /**
     * Выводим данные для показа, когда начался Debug
     * @param $data
     */
    public static function printDebug($data)
    {
        if (defined('DEBUG')) {
            self::printData($data);
        }
    }
}

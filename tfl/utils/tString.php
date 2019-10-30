<?php

namespace tfl\utils;

class tString
{
    const RESPONSE_OK = 'Ok';
    const RESPONSE_ERROR = 'Error';
    const RESPONSE_RESULT_SUCCESS = 1;
    const RESPONSE_RESULT_ERROR = 0;

    public static function serialize($value = null)
    {
        return serialize($value);
    }

    public static function unserialize($value = null)
    {
        if (!$value) {
            return null;
        }

        return @unserialize($value);
    }

    public static function checkString($value, $sql = false)
    {
        //@todo Добавить защитцу
        if (is_array($value)) {
            return $value;
        }

        return trim($value);
    }

    public static function checkNum($value, $sql = false)
    {
        return (int)$value;
    }

    public static function getCurentDate()
    {
        $date = new \DateTime();

        return $date->format('Y-m-d H:i:s');
    }

    public static function checkDatetime($value = null, $sql = false)
    {
        if (!$value) {
            return null;
        }
        $date = new \DateTime($value);

        if (strtotime($value) <= 0) {
            return null;
        }

        return $date->format('Y-m-d H:i:s');
    }

    public static function getDatetime($value, $format = 'd.m.Y')
    {
        if (self::checkDatetime($value)) {
            $date = new \DateTime($value);
            return $date->format($format);
        }

        return null;
    }

    public static function getStringLength($value = null)
    {
        return mb_strlen($value);
    }

    /**
     * Обработываем textarea при получении из ДБ
     *
     * @param $text
     * @return mixed
     */
    public static function fromDbTextarea($text)
    {
        $bb = array(PAGE_EOL);
        $tag = array('<br/>');
        $text = str_ireplace($tag, $bb, $text);

        //$text = str_replace(PAGE_BR, PAGE_EOL, $text);
        //$text = str_replace("<br>", PAGE_EOL, $text);

        return $text;
    }

    /**
     * Записываем textarea в БД
     *
     * @param $text
     * @return string|string[]|null
     */
    private static function toDbTextarea($text)
    {
        //$bb = array('\r\n','\n','\r');
        //$text = str_replace($bb, PAGE_BR, $text);

        /*
        $text = str_replace('\n', PAGE_BR, $text);
        $text = str_replace('\r', PAGE_BR, $text);
        $text = str_replace('\r\n', PAGE_BR, $text);
        */

        $text = preg_replace('!\\r\\n!si', PAGE_BR, $text);
        $text = preg_replace('!\\n!si', PAGE_BR, $text);
        $text = preg_replace('!\\r!si', PAGE_BR, $text);

        return $text;
    }

    /**
     * @param null $text
     * @param bool $empty true - заменять пробелы, false - оставлять пробелы
     * @return mixed|null|string
     */
    public static function toTranslit($text = null, $empty = false)
    {
        if (!$text) {
            return $text;
        }

        $text = htmlspecialchars(trim($text), ENT_QUOTES);
        $text = str_replace("\xC2xA0", " ", $text);

        $rus = ['а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', '/', '~', '`', '$', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', ':', '[', ']', '(', ')', '-', '?', '&', '%C2%A0', '№', ',', '#039;', 'quot;', 'amp;', '__', '&#37;', '&#39;', '&#33;', '!', '%', '«', '»', '|', '*'];

        $eng = ['a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sh', '', 'i', '', 'e', 'u', 'ya', '_', '', '', '&#036;', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sh', '', 'i', '', 'e', 'u', 'ya', '', '', '', '', '', '_', '', '', '_', '', '_', '', '', '', '_', '', '', '', '', '', '', '', '', ''];

        if ($empty) {
            $rus[] = ' ';
            $eng[] = '_';
            $rus[] = '&nbsp';
            $eng[] = '_';
        }

        $text = str_replace($rus, $eng, $text);
        $text = str_replace(chr(194) . chr(160), '_', $text);

        if ($empty) {
            $text = urlencode($text);
        }

        return strtolower($text);
    }

    public static function isEmail($email)
    {
        return preg_match("|[0-9a-z_]+@[0-9a-z_\-^\.]+\.[a-z]{2,3}|i", $email);
    }

    public static function isUrl($url)
    {
        return preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url);
    }
}
<?php

namespace tfl\utils;

class tDebug
{
    public static function printData($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        exit;
    }
}

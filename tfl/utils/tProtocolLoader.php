<?php

namespace tfl\utils;

class tProtocolLoader
{
    public static function closeAccess($status = 500, $text = null)
    {
        $text = $text ?? 'Not Found';

        header($_SERVER["SERVER_PROTOCOL"] . ' ' . $status . ' ' . $text);
        header('Status: ' . $status . ' ' . $text);
        exit;
    }
}
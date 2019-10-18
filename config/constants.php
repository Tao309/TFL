<?php

$config = [];
//Домен по умолчанию
$config['HTTP'] = 'http';
$config['DOMEN'] = 'tfl';

$root = (isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : $config['DOMEN'];
define('ROOT', $config['HTTP'] . '://' . $root . '/');

$zroot = rtrim($_SERVER['DOCUMENT_ROOT'], 'web');
define('zROOT', $zroot . '/');

if (!defined('PAGE_EOL')) {
    switch (strtoupper(substr(PHP_OS, 0, 3))) {
        case 'WIN':
            define('PAGE_EOL', "\r\n");
            break;
        case 'DAR':
            define('PAGE_EOL', "\r");
            break;
        default:
            define('PAGE_EOL', "\n");
    }
}

define('PAGE_BR', '<br/>');
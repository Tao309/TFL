<?php
use \tfl\utils\tFile;

$zroot = rtrim($_SERVER['DOCUMENT_ROOT'], WEB_PATH);
define('zROOT', $zroot);

/**
 * @todo Сделать красивый вид и вынести в другие методы
 */

spl_autoload_register('autoload_register');

function autoload_register($className): void
{
    $file = null;

    $names = explode("\\", $className);
    $names = array_map('strtolower', $names);

    $file = zROOT . implode('/', $names) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
}



// set_exception_handler

// set_error_handler
<?php
if (!defined('INCLUDE')) exit;
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
//    $names = array_map('strtolower', $names);

    if (count($names) > 2) {
        if ($names[0] == 'app' && $names[1] == 'models') {
            $fileName = $names[2];
            $names[2] = mb_strtolower($names[2]);

            if (count($names) > 3) {
                $file = zROOT . implode('/', $names) . '.php';
            } else {
                $file = zROOT . implode('/', $names) . '/' . $fileName . '.php';
            }
        }
    }

    if (!$file) {
        $file = zROOT . implode('/', $names) . '.php';
    }

    if (file_exists($file)) {
        require_once $file;
    }
}


set_exception_handler('exception_handler');

function exception_handler(Throwable $e)
{
    echo $e->getMessage();

    $trace = $e->getTrace();

    echo '<pre>';
    print_r($trace);
    echo '</pre>';
}

//TFLNotFoundModel

// set_error_handler

<?php
if (!defined('INCLUDE')) exit;
use \tfl\utils\tFile;
use \tfl\utils\tHtmlTags;

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
    return;
}

//TFLNotFoundModel

set_error_handler('error_handler');

function error_handler($level, $message, $file, $line, $context, $class = '')
{
    error_reporting(E_ALL);
    //error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 1);

    $error = "lvl: " . $level . " | msg:" . $message . " | file:" . $file . " | ln:" . $line;

    switch ($level) {
        case E_ERROR:
            $type = 'Error';
            break;
        case E_WARNING:
            $type = 'Warning';
            break;
        case E_NOTICE:
            $type = 'Notice';
            break;
        default;
            $type = 'ex';
    }

    $file = str_replace("\\", "/", $file);
    $file = preg_replace("!" . zROOT . "!msi", "/", $file);

    $t = tHtmlTags::startTag('div', [
        'class' => $class,
    ]);
    $t .= tHtmlTags::startTag('div');
    $t .= tHtmlTags::render('span', $type, ['class' => 'title']);
    $t .= tHtmlTags::render('span', $message, ['class' => 'message']);
    $t .= tHtmlTags::endTag('div');
    $t .= tHtmlTags::startTag('div');
    $t .= tHtmlTags::render('span', 'File:', ['class' => 'title']);
    $t .= tHtmlTags::render('span', $file, ['class' => 'file']);
    $t .= tHtmlTags::render('span', $line, ['class' => 'line']);
    $t .= tHtmlTags::endTag('div');
    $t .= tHtmlTags::endTag('div');

    //echo "<p><strong>Context</strong>: $". join(', $', array_keys($context))."</p>";

    echo $t;
    return;
}

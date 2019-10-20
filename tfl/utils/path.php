<?php

namespace tfl\utils;

class Path
{
    public function __construct()
    {
        // @todo Сделать один раз вывод
        $config = require_once zROOT . 'config/web.php';

        if (isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
            $root = rtrim($_SERVER['SERVER_NAME'], WEB_PATH);
        } else {
            $root = $config['DOMEN'];
        }

        define('ROOT', $config['HTTP'] . '://' . $root . '/');

//        $zroot = rtrim($_SERVER['DOCUMENT_ROOT'], WEB_PATH);
//        define('zROOT', $zroot);
    }

    public function getWebPath(): string
    {
        return ROOT;
    }

    public function getDocumentPath(): string
    {
        return zROOT;
    }
}

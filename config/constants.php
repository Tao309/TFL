<?php

define('WEB_PATH', 'web');

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
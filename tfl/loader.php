<?php
session_start();

require_once '../config/constants.php';
require_once 'autoloader.php';

require_once 'tfl.php';

$tfl = new TFL;
$tfl->launchAfterInit();

if (TFL::source()->session->isUser()) {
    echo 'Авторизован как ' . TFL::source()->session->currentUser()->login;
}
<?php

require_once '../config/constants.php';
require_once 'autoloader.php';

require_once 'tfl.php';

$tfl = new TFL;
$tfl->section->launch();
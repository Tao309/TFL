<?php

require_once __DIR__ . '/../tfl/loader.php';


$rows = TFL::source()->db
    ->select('*')
    ->from('user')
    ->findAll();

print_r($rows);
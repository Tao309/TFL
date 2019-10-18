<?php

require_once __DIR__ . '/../tfl/loader.php';

$user = \app\models\User::getById(2);
//$user = new \app\models\User();

//echo '<pre>';
//var_dump($user);
//echo '</pre>';


$view = new \app\views\models\User\DetailsView($user, 'view');

echo $view->render();
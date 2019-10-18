<?php

namespace tfl\utils;

class DB {
    const TYPE_INSERT = 'insert';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';
    const TYPE_SAVE = 'save';
    const TYPE_ERROR = 'error';

    private $type;
    private $lastInsertId = 0;

    /*
     * @var PDO
     */
    private $pdo;


}
<?php

class TFL {
    /**
     * @var TFL
     */
    private static $source;

    public function __construct()
    {
        self::launchSource();
    }

    private function launchSource(): void
    {
        $this->db = new \tfl\utils\DB();

//        $this->request;
//        $this->session;

        self::$source = $this;
    }

    public static function source(): TFL
    {
        return self::$source;
    }
}
<?php

/**
 * Class TFL
 *
 * @property \tfl\utils\Path path
 * @property \tfl\utils\DB db
 */
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
        $this->path = new \tfl\utils\Path();
        $this->db = new \tfl\utils\DB();

//        $this->request;
//        $this->session;

        self::$source = $this;
    }

    public static function source(): TFL
    {
        return self::$source;
    }

    public function config(string $fileName)
    {
        $file = zROOT . 'config/' . $fileName . '.php';
        if (\tfl\utils\tFile::file_exists($file)) {
            return require_once $file;
        }

        return [];
    }
}
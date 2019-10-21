<?php

/**
 * Class TFL
 *
 * @property \tfl\utils\Path path
 * @property \tfl\utils\DB db
 * @property \tfl\builders\RequestBuilder request
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
        $this->path = new \tfl\builders\PathBuilder();
        $this->db = new \tfl\builders\DbBuilder();

        $this->request = new \tfl\builders\RequestBuilder();
//        $this->session;

        self::$source = $this;

        $this->initControllers();
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

    private function initControllers(): void
    {
        new \tfl\builders\InitControllerBuilder();
    }
}
<?php

/**
 * Class TFL
 *
 * @property \tfl\builders\PathBuilder path
 * @property \tfl\builders\DbBuilder db
 * @property \tfl\builders\RequestBuilder request
 * @property \tfl\builders\InitControllerBuilder section
 * @property \tfl\auth\Session session
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
        $this->db = new \tfl\builders\DbBuilder();
        $this->path = new \tfl\builders\PathBuilder();
        $this->request = new \tfl\builders\RequestBuilder();

        self::$source = $this;
    }

    public static function source(): TFL
    {
        return self::$source;
    }

    public function getVersion(): string
    {
        return '1.00.000';
    }

    public function config(string $fileName)
    {
        $file = zROOT . 'config/' . $fileName . '.php';
        if (\tfl\utils\tFile::file_exists($file)) {
            return require_once $file;
        }

        return [];
    }

    public function launchAfterInit()
    {
        self::$source->session = new \tfl\auth\Session();
        self::$source->section = new \tfl\builders\InitControllerBuilder();
    }
}
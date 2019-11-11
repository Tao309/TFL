<?php
if (!defined('INCLUDE')) exit;

/**
 * Class TFL
 *
 * @property \tfl\builders\PathBuilder path
 * @property \tfl\builders\DbBuilder db
 * @property \tfl\builders\RequestBuilder request
 * @property \tfl\builders\InitControllerBuilder section
 * @property \tfl\auth\Session session
 * @property \tfl\builders\PartitionBuilder partition
 */
class TFL
{
    /**
     * @var TFL
     */
    private static $source;
    /**
     * @var array
     */
    private static $config;

    public function __construct()
    {
        self::launchSource();
    }

    private function launchSource(): void
    {
	    \tfl\utils\tRoute::init();

        $this->db = new \tfl\builders\DbBuilder();
        $this->path = new \tfl\builders\PathBuilder();
        $this->request = new \tfl\builders\RequestBuilder();

        self::$source = $this;
    }

    public static function source(): TFL
    {
        return self::$source;
    }

    public function launchAfterInit()
    {
	    \tfl\utils\tRoute::initModelRestRoute();
        new \tfl\builders\CacheBuilder();

        self::$source->session = new \tfl\auth\Session();
	    self::$source->partition = new \tfl\builders\PartitionBuilder();
        self::$source->section = new \tfl\builders\InitControllerBuilder();
    }

	public function enableCsrfProtection()
	{
		\tfl\utils\tCrypt::enableCsrfToken();
	}

    public function getVersion(): string
    {
        return '1.00.000';
    }

    public function config(string $fileName)
    {
        $file = zROOT . 'config/' . $fileName . '.php';
        if (\tfl\utils\tFile::file_exists($file)) {
            if (!isset(self::$config[$fileName])) {
                self::$config[$fileName] = require $file;
            }

            return self::$config[$fileName];
        }

        return [];
    }

    private static $options = [];

    private function checkOptionExists($type): void
    {
        if (!isset(self::$options[$type])) {
            self::$options[$type] = \tfl\utils\tCaching::getOptionData($type);
        }
    }

    public function getOptionValue(string $type, string $name)
    {
        $this->checkOptionExists($type);

        return self::$options[$type][$name] ?? null;
    }

    public function setOptionValue(string $type, string $name, $value): void
    {
        $this->checkOptionExists($type);

        self::$options[$type][$name] = $value;
    }

}
<?php

namespace tfl\utils;

use tfl\units\UnitOption;

class tCaching
{
    const FILE_EXT = 'tmp';
    const FILE_PATH = zROOT . 'tmp/';
    const FILE_OPTION_PATH = self::FILE_PATH . 'option/';

    public static function recreateUnitOptionFiles(array $models)
    {
        foreach ($models as $model) {
            $file = $model->getFileName() . '.' . self::FILE_EXT;

            tFile::removeIfExists(self::FILE_OPTION_PATH . $file);

            tFile::writeFile(self::FILE_OPTION_PATH, $file, $model->getJustOptionsList(), true);
        }
    }

    public static function isOptionFileExists($name)
    {
        return tFile::file_exists(self::FILE_OPTION_PATH . $name . '.' . self::FILE_EXT);
    }

    public static function getOptionData($name)
    {
        return tFile::readFile(self::FILE_OPTION_PATH, $name . '.' . self::FILE_EXT);
    }
}
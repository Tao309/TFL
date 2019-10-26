<?php

namespace tfl\builders;

use tfl\units\UnitOption;
use tfl\utils\tCaching;

class CacheBuilder
{
    public function __construct()
    {
        $this->init();
    }

    private function init(): void
    {
        $this->checkOptions();
    }

    /**
     * Проверяем наличие всех файлов от UnitOption
     */
    private function checkOptions(): void
    {
        $names = UnitOption::getOptionTitles();
        foreach ($names as $index => $name) {
            if (tCaching::isOptionFileExists($name)) {
                unset($names[$index]);
            }
        }

        //Создаём настройки тех, чьих tmp файлов нет
        if (!empty($names)) {
            tCaching::recreateUnitOptionFiles(UnitOption::getByNames($names));
        }
    }
}

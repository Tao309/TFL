<?php

namespace tfl\utils;

class tObfuscator
{
    private static $cssPrefix = [
        '',
//        'o',
        'moz',
        'webkit',
//        'khtml',
        'ms',
    ];
    private static $cssConstants;

    public static function compress_code($content = null)
    {
        if (empty($content)) {
            return null;
        }

        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        $content = str_replace(array("\r\n", '  ', '    ', '    ', '     ', "\r", "\n", "\t"), ' ', $content);
        $content = str_replace(array('    ', '    ', '     '), '', $content);
        $content = str_replace(array('> <', '>  <', '>   <'), '><', $content);

        $content = preg_replace('!\<\!\-\-(.*?)\-\-\>!', '', $content);

        return trim($content);
    }

    public static function compressCSS($content = null)
    {
        if (empty($content)) {
            return null;
        }

        $content = preg_replace('/(\/\*).*?(\*\/)/s', '', $content);
        $content = preg_replace('/([^\w\'\"])\s+/', '\\1 ', $content);
        $content = preg_replace('/\s\s/', ' ', $content);

        //@todo Заменям пробелы в начале и в конце внутри {}
        //@todo .field {}(вот тут пробелы убрать).name {}

        return self::compress_code($content);
    }

    public static function compressJS($content = null)
    {
        if (empty($content)) {
            return null;
        }

        //Убираем // со строкой
        $content = preg_replace('/(\/\/).*?\n/s', '', $content);
        $content = preg_replace('/(\/\/).*?\r/s', '', $content);

        //Убираем комментарий /**/
        $content = preg_replace('/(\/\*).*?(\*\/)/s', '', $content);

        //Заменяем перенос строк на пробел
        $content = preg_replace('/\s*(\r\n|\n\r|\n)\s*/', ' ', $content);
        //$content = preg_replace('/\s*(\r\n)\s*/', '', $content);

        //$content = preg_replace('/\;\}/s', ";}\r\n", $content);
        //$content = preg_replace('/\$\(/s', "\r\n$(", $content);

        //Заменяем двойные пробелы на один
        $content = preg_replace('/\s\s/', ' ', $content);

        return self::compress_code($content);
    }

    /**
     * Замена свойств в CSS на кроссбарузерные
     *
     * @param null $content
     * @return string|null
     */
    public static function replaceCssProperties(&$content): void
    {
        $standardAttrList = [
            'border-radius',
            'box-shadow',
            'opacity',
            'box-sizing',
            'transition',

            'text-overflow',
            'filter',
            'text-rendering',
            'font-smoothing',

            'tap-highlight-color',
            'user-select',
            'text-size-adjust',
            'focus-ring-color',

            'flex',
        ];

        foreach ($standardAttrList as $attr) {
            $content = preg_replace_callback("!(" . $attr . "):(.*?);!msi", function ($matches) {
                $content = '';

                $prefixes = self::$cssPrefix;
                switch ($matches[1]) {
                    case 'opacity':
                        $prefixes = ['', 'moz', 'webkit'];
                        break;
                    case 'filter':
                        $prefixes = ['', 'ms'];
                        break;
                }

                foreach ($prefixes as $prefix) {
                    if (!empty(trim($prefix))) {
                        $prefix = '-' . $prefix . '-';
                    }
                    $content .= $prefix . $matches[1] . ':' . trim($matches[2]) . ';';
                }

                switch ($matches[1]) {
                    case 'opacity':
                        $content .= "filter:alpha(opacity=" . ($matches[2] * 100) . ");";
                        break;
                }

                return $content;
            }, $content);
        }
    }

    /**
     * Переменные цветов и др для замены в css файлах
     * @return array
     */
    public static function getCssConstants(): array
    {
        if (is_null(self::$cssConstants)) {
            self::$cssConstants = \TFL::source()->config('cssConstants');
        }

        return self::$cssConstants;
    }
    /**
     * Замена переменных в контенте
     *
     * @param string $content
     * @param array $constants
     * @return string
     */
    public static function replaceCssConstants(&$content): void
    {
        $constants = self::getCssConstants();
//        print_r($constants);exit;

        $search = [];
        $replace = [];

        foreach ($constants as $type => $values) {
            if (empty($values) || !is_array($values)) {
                continue;
            }
            $name = '$' . $type;

            foreach ($values as $index => $colorData) {
                $search[] = $name . '[' . $index . ']';
                $replace[] = $colorData['value'];
            }
        }

        $content = str_ireplace($search, $replace, $content);
    }
}
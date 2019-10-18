<?php

namespace tfl\utils;

class tFile
{
//    private static $existingFiles = [];

    public static function file_exists(string $filePath): bool
    {
        return @file_exists($filePath);
    }

    public static function rename(string $oldFile, string $newFile): void
    {
        rename(zROOT . $oldFile, zROOT . $newFile);
    }

    public static function checkDirExists(string $dir): bool
    {
        $dir = preg_replace("/^\/*|\/*$/", '', $dir);
        $values = explode('/', $dir);

        //@todo Exception
        if (!count($values)) {
            die('Not values for path: ' . $dir);
        }

        $dir = zROOT;

        foreach ($values as $index => $value) {
            $dir .= $value . '/';

            if (!self::is_dir($dir)) {
                self::createDir($dir);
            }
        }

        return true;
    }

    public static function createDir(string $path): void
    {
        if (!self::is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    public static function file_get_contents(string $filePath): string
    {
        return file_get_contents($filePath);
    }

    public static function filemtime(string $pathName): int
    {
        return filemtime($pathName);
    }

    public static function is_dir(string $pathName): bool
    {
        return is_dir($pathName);
    }

    /*
        private static function getFilePathName($path, $name, $isOption = false)
        {
            if(!preg_match('!(.*?)/$!i', $path))
            {
                $path .= '/';
            }
            //@todo Проверка на zROOT в начале

            $path = zROOT.$path;
            //Проверяем наличие папки
            if(!self::is_dir($path))
            {
                //@todo Сделать через Exception
                die('Не найден путь: <b>'.$path.'</b> для файла <b>'.$name.'</b>');
            }

            if(!$isOption)
            {
                return $path.$name;
            }

            //Название файла в camelCase
            $name = \Models\Option::nameToFileName($name);

            return $path.$name.'.tmp';
        }
        public static function writeFile($path, $name, $data, $serialize = true)
        {
            //@todo Проверить на конце $path '/'
            $pathName = self::getFilePathName($path, $name);

            $handle = fopen($pathName, 'w');
            flock($handle,LOCK_EX);

            if($serialize)
            {
                $data = tString::serialize($data);
            }

            fwrite($handle, $data);
            fclose($handle);
        }
        public static function rewriteFile($path, $name, $data, $serialize = true) {
            $pathName = self::getFilePathName($path, $name);

            $handle = fopen($pathName, 'r+');
            flock($handle,LOCK_EX);

            if($serialize)
            {
                $data = tString::serialize($data);
            }

            fwrite($handle, $data);
            fclose($handle);
        }
        public static function readFile($path, $name)
        {
            $pathName = self::getFilePathName($path, $name);

            if (self::file_exists($pathName)) {
                $handle = fopen($pathName, 'rb');
                flock($handle,LOCK_EX);
                $size = filesize($pathName);

                if($size <= 0) {
                    return null;
                }

                $variable = fread($handle, $size);
                fclose($handle);

                if($var = tString::unserialize($variable)) {
                    return $var;
                }
            }

            return null;
        }
        public static function checkFilePeriod($path, $name, $period) {
            $pathName = self::getFilePathName($path, $name);

            //Время не вышло
            $input = 0;
            if(!self::file_exists($pathName)) {
                return $input;
            }

            $need_create = (time() - $period);
            $create_time = self::filemtime($pathName);

            //Время вышло
            if($need_create >= $create_time) {
                $input = 1;
            }

            return $input;
        }
        public static function deleteFile($path, $name, $isOption = false) {
            //@todo Проверка $path, где можно удалять, чтобы не удалить важные файлы
            $pathName = self::getFilePathName($path, $name, $isOption);

            if(!self::file_exists($pathName)) {
                return null;
            }

            @unlink($pathName);
        }
        */

    public static function getimagesize(string $path)
    {
        return getimagesize($path);
    }

    public static function getInfoByPath(string $path): array
    {
        /*
         Array
        (
            [dirname] => upload/user/cover/mini
            [basename] => 51_1552739739.png
            [extension] => png
            [filename] => 51_1552739739
        )
         */
        $data = pathinfo($path);

        return $data;
    }

}
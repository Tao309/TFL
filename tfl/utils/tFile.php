<?php

namespace tfl\utils;

class tFile
{
//    private static $existingFiles = [];

    public static function file_exists(string $filePath): bool
    {
        return @file_exists($filePath);
    }

    public static function removeIfExists($filePath): bool
    {
        if (self::file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    public static function rename(string $oldFile, string $newFile): void
    {
        rename(zROOT . $oldFile, zROOT . $newFile);
    }

    public static function copyFile($sourceFile, $destFile)
    {
        return copy($sourceFile, $destFile);
    }

    public static function checkDirExists(string $dir): bool
    {
        $dir = preg_replace("/^\/*|\/*$/", '', $dir);
	    $values = explode(DIR_SEP, $dir);

        if (!count($values)) {
	        return false;
        }

        $dir = zROOT;

        foreach ($values as $index => $value) {
	        $dir .= $value . DIR_SEP;

            self::createDir($dir);
        }

        return true;
    }

    public static function createDir(string $path): void
    {
        if (!self::is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    public static function file_get_contents(string $filePath)
    {
        return file_get_contents($filePath);
    }

    public static function file_put_contents(string $filePath, $content)
    {
        return file_put_contents($filePath, $content);
    }

    public static function filemtime(string $pathName): int
    {
        return filemtime($pathName);
    }

    public static function is_dir(string $pathName): bool
    {
        return is_dir($pathName);
    }

//        private static function getFilePathName($path, $name, $isOption = false)
//        {
//            if(!preg_match('!(.*?)/$!i', $path))
//            {
//                $path .= DIR_SEP;
//            }
//            //@todo Проверка на zROOT в начале
//
//            $path = zROOT.$path;
//            //Проверяем наличие папки
//            if(!self::is_dir($path))
//            {
//                //@todo Сделать через Exception
//                die('Не найден путь: <b>'.$path.'</b> для файла <b>'.$name.'</b>');
//            }
//
//            if(!$isOption)
//            {
//                return $path.$name;
//            }
//
//            //Название файла в camelCase
//            $name = \Models\Option::nameToFileName($name);
//
//            return $path.$name.'.tmp';
//        }
        public static function writeFile($path, $name, $data, $serialize = true)
        {
//            $pathName = self::getFilePathName($path, $name);

            $handle = fopen($path . $name, 'w');
            flock($handle,LOCK_EX);

            if($serialize)
            {
                $data = tString::serialize($data);
            }

            fwrite($handle, $data);
            fclose($handle);
        }
//        public static function rewriteFile($path, $name, $data, $serialize = true) {
//            $pathName = self::getFilePathName($path, $name);
//
//            $handle = fopen($pathName, 'r+');
//            flock($handle,LOCK_EX);
//
//            if($serialize)
//            {
//                $data = tString::serialize($data);
//            }
//
//            fwrite($handle, $data);
//            fclose($handle);
//        }
    public static function readFile($path, $name, $serialize = true)
    {
//            $pathName = self::getFilePathName($path, $name);
        $pathName = $path . $name;

            if (self::file_exists($pathName)) {
                $handle = fopen($pathName, 'rb');
                flock($handle,LOCK_EX);
                $size = filesize($pathName);

                if($size <= 0) {
                    return null;
                }

                $variable = fread($handle, $size);
                fclose($handle);

                if ($serialize) {
                    return tString::unserialize($variable);
                }

                return $variable;
            }

            return null;
        }
//        public static function checkFilePeriod($path, $name, $period) {
//            $pathName = self::getFilePathName($path, $name);
//
//            //Время не вышло
//            $input = 0;
//            if(!self::file_exists($pathName)) {
//                return $input;
//            }
//
//            $need_create = (time() - $period);
//            $create_time = self::filemtime($pathName);
//
//            //Время вышло
//            if($need_create >= $create_time) {
//                $input = 1;
//            }
//
//            return $input;
//        }
//        public static function deleteFile($path, $name, $isOption = false) {
//            //@todo Проверка $path, где можно удалять, чтобы не удалить важные файлы
//            $pathName = self::getFilePathName($path, $name, $isOption);
//
//            if(!self::file_exists($pathName)) {
//                return null;
//            }
//
//            @unlink($pathName);
//        }

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
<?php

namespace tfl\handlers;

class ImageUploadHandler
{
    const SAVE_PATH = 'upload/';
    const CACHE_PATH = 'cache/';

    const FILE_NAME_NO_FOTO = 'nofoto';
    const FILE_NAME_NO_IMAGE = 'noimage';
    const FILE_NAME_DEFAULT_AVATAR = 'default_avatar';

    const FILE_EXT_PNG = 'png';
    const FILE_EXT_JPG = 'jpg';

    public static function generateNoFoto(int $width)
    {
        return self::generateNoImage($width, self::FILE_NAME_NO_FOTO);
    }

    public static function generateNoImage(int $width, $type = self::FILE_NAME_NO_IMAGE,
                                           $ext = self::FILE_EXT_PNG)
    {
        return '';
    }

    public static function generateCacheImage()
    {

    }

    public static function createImage()
    {

    }
}

<?php

namespace tfl\handlers\upload;

abstract class UploadHandler
{
    const SAVE_PATH = 'upload/';
    const CACHE_PATH = 'cache/';

    const FILE_EXT_PNG = 'png';
    const FILE_EXT_JPG = 'jpg';
    const FILE_EXT_JPEG = 'jpeg';
    const FILE_EXT_GIF = 'gif';

    abstract public function upload();
}

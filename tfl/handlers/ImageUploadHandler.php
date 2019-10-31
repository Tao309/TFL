<?php

namespace tfl\handlers;

use app\models\Image;
use tfl\utils\tFile;
use tfl\utils\tString;

/**
 * Class ImageUploadHandler
 * @package tfl\handlers
 *
 * @property array $file
 * @property resource $fileData
 * @property int $model_id
 * @property string $model_name
 * @property string $model_attr
 * @property int $id
 *
 * @property string $fileName
 */
class ImageUploadHandler extends UploadHandler
{
    const FILE_NAME_NO_FOTO = 'nofoto';
    const FILE_NAME_NO_IMAGE = 'noimage';
    const FILE_NAME_DEFAULT_AVATAR = 'default_avatar';

    const FILE_EXT_PNG = 'png';
    const FILE_EXT_JPG = 'jpg';
    const FILE_EXT_JPEG = 'jpeg';
    const FILE_EXT_GIF = 'gif';

    private $file;
    private $fileData;
    private $model_name;
    private $model_id;
    private $model_attr;
    private $id;

    /**
     * @var string|null
     */
    private $fileName;
    /**
     * @var bool
     */
    private $useWaterMark = false;
    /*
     * @var array
     */
    private $errorText = [];
    /**
     * Настройки размеров для загрузки
     * @var array
     */
    private $sizeData = [];

    /**
     * вычисление параметров сторон при обрезке
     * @param $needParams
     * @param $origParams
     * @return array
     */
    private static function getCropParams(array $needParams, array $origParams): array
    {
        $widthScale = ($origParams[0] / $needParams[0]);
        $heightScale = ($origParams[1] / $needParams[1]);

        //Проверка сторон need

        //Проверка сторон оригинала

        //Сравнение длин и высот

        $minScale = min([$widthScale, $heightScale]);
        $maxScale = max([$widthScale, $heightScale]);

        return [
            tString::checkNum($origParams[0] / $maxScale),
            tString::checkNum($origParams[1] / $maxScale),
        ];
    }

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

    /**
     * Получаем расширение файла из mime типа
     * @param $type
     * @return string
     */
    public static function getExtType($type)
    {
        $type = end(explode('/', $type));

        if (in_array($type, [self::FILE_EXT_JPEG, self::FILE_EXT_JPG])) {
            $type = self::FILE_EXT_JPG;
        }

        return $type;
    }

    public function __construct(Image $model)
    {
        $this->fileData = $model->fileData;
        $this->model_name = $model->model_name;
        $this->model_id = $model->model_id;
        $this->model_attr = $model->model_attr;
        $this->id = $model->id;

        $this->setImageData();
        $this->setSizeData();
    }

    public function setSizeData(array $data = [])
    {
        $this->sizeData = [
            Image::NAME_SIZE_MINI => [$data[Image::NAME_SIZE_MINI][0] ?? 40, $data[Image::NAME_SIZE_MINI][1] ?? 40],
            Image::NAME_SIZE_NORMAL => [$data[Image::NAME_SIZE_NORMAL][0] ?? 150, $data[Image::NAME_SIZE_NORMAL][1] ?? 150],
            Image::NAME_SIZE_FULL => [$data[Image::NAME_SIZE_FULL][0] ?? 360, $data[Image::NAME_SIZE_FULL][1] ?? 360],
        ];
    }

    private function setImageData()
    {
        list($width, $height) = tFile::getimagesize($this->fileData['tmp_name']);

        $this->file = [
            'filename' => preg_replace('!(.*?)\.(.*?)$!si', '$1', $this->fileData['name']),
            'extension' => self::getExtType($this->fileData['type']),
            'size' => $this->fileData['size'],
            'tempFile' => $this->fileData['tmp_name'],
            'width' => $width,
            'height' => $height,
        ];
    }

    //@todo Поставить в interface addErrorText, getErrorText
    private function addErrorText(string $message): void
    {
        $this->errorText[] = $message;
    }

    public function getErrorText(): string
    {
        return implode(PAGE_EOL, $this->errorText);
    }

    private function checkRequireFields()
    {
        if (
            empty($this->fileData) || empty($this->model_name)
            || empty($this->model_id) || empty($this->model_attr) || empty($this->id)
        ) {
            $this->addErrorText('Required fields: file, model name and id and attr');
            return false;
        }

        return true;
    }

    private function imageCreateFrom()
    {
        switch ($this->file['extension']) {
            case self::FILE_EXT_JPG:
            case self::FILE_EXT_JPEG:
                $image = @imagecreatefromjpeg($this->file['tempFile']);
                break;
            case self::FILE_EXT_PNG:
                $image = @imagecreatefrompng($this->file['tempFile']);
                break;
            case self::FILE_EXT_GIF:
                $image = @imagecreatefromgif($this->file['tempFile']);
                break;
            default:
                $this->addErrorText("Can not upload format file <b>{$this->file['extension']}</b>");
                return false;
        }

        if (!$image) {
            $this->addErrorText('Uploaded file is corrupted');
            return false;
        }

        return $image;
    }

    private function checkDirExists(string $dirPath): void
    {
        tFile::checkDirExists($dirPath);

        tFile::checkDirExists($dirPath . Image::NAME_SIZE_MINI);
        tFile::checkDirExists($dirPath . Image::NAME_SIZE_NORMAL);
        tFile::checkDirExists($dirPath . Image::NAME_SIZE_FULL);
    }

    private function getDataSizes(): array
    {
        return $this->sizeData;
    }

    public function useWaterMark()
    {
        $this->useWaterMark = true;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function upload()
    {
        if (!$this->checkRequireFields()) {
            return false;
        }

        $image = $this->imageCreateFrom();

        if (!$image) {
            return false;
        }

        $dirPath = WEB_PATH . '/upload/' . $this->model_name . '/' . $this->model_attr . '/';
        $this->checkDirExists($dirPath);
        $zPath = zROOT . $dirPath;

        foreach ($this->getDataSizes() as $sizeType => $dataSize) {
            $path = $zPath . $sizeType . '/';

            list($width, $height) = self::getCropParams([$dataSize[0], $dataSize[1]],
                [$this->file['width'], $this->file['height']]);
            $this->fileName = $this->id . '_' . time() . '.' . $this->file['extension'];

            $imageTemp = imagecreatetruecolor($width, $height);

            if (!$this->useWaterMark) {
                imagealphablending($imageTemp, false);
                imagesavealpha($imageTemp, true);
            }

            imagecopyresampled($imageTemp, $image, 0, 0, 0, 0,
                $width, $height, $this->file['width'], $this->file['height']);

            switch ($this->file['extension']) {
                case self::FILE_EXT_JPG:
                case self::FILE_EXT_JPEG:
                    imagejpeg($imageTemp, $path . $this->fileName, 100);
                    break;
                case self::FILE_EXT_PNG:
                    imagepng($imageTemp, $path . $this->fileName, PNG_NO_FILTER);
                    break;
                case self::FILE_EXT_GIF:
                    imagegif($imageTemp, $path . $this->fileName);
                    break;
            }

            imagedestroy($imageTemp);
        }

        return true;
    }
}

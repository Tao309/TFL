<?php

namespace tfl\handlers\upload;

use app\models\Image;
use tfl\units\Unit;
use tfl\units\UnitActive;
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
    /*
     * Настройки для ручной обрезки при выборе с модального окна
     * @var array
     */
    private $manualCropData = [];

    /**
     * Стандартная обрезка. Оставляет соотношение сторон оригинала
     * @param array $needParams
     * @param array $origParams
     */
    private static function standardCrop(array $needParams, array $origParams): array
    {
        list($finalWidth, $finalHeight) = $needParams;

        if (($origParams[0] > $needParams[0]) || ($origParams[1] > $needParams[1])) {
            $ratio_orig = $origParams[0] / $origParams[1];
            if ($needParams[0] / $needParams[1] > $ratio_orig) {
                $finalWidth = $needParams[1] * $ratio_orig;
            } else {
                $finalHeight = $needParams[0] / $ratio_orig;
            }
        } else {
            list($finalWidth, $finalHeight) = $origParams;
        }

        return [$finalWidth, $finalHeight, $origParams[0], $origParams[1], 0, 0];
    }

    /**
     * Обрезает до нужных размеров сторон
     * @param array $needParams
     * @param array $origParams
     * @return array
     */
    private static function advancedCrop(array $needParams, array $origParams): array
    {
        $widthScale = ($origParams[0] / $needParams[0]);
        $heightScale = ($origParams[1] / $needParams[1]);

        $minScale = min([$widthScale, $heightScale]);
//        $maxScale = max([$widthScale, $heightScale]);

        $src_x = ($origParams[0] / 2) - (($needParams[0] * $minScale) / 2);
        $src_y = ($origParams[1] / 2) - (($needParams[1] * $minScale) / 2);

        return [$needParams[0], $needParams[1],
            ($needParams[0] * $minScale), ($needParams[1] * $minScale),
            $src_x, $src_y];
    }

    /**
     * Ручная обрезка по выбранным параметрам в модальном окне
     * @param array $needParams
     * @param array $origParams
     * @param array $cropData
     */
    private static function manualCrop(array $needParams, array $origParams, array $cropData)
    {
        $src_x = $src_y = 0;
        list($finalWidth, $finalHeight) = $needParams;
        list($origWidth, $origHeight) = $origParams;

        $cropScale = $cropData['cropScale'] ?? 1;
        $cropLeft = $cropData['cropLeft'] ?? 0;
        $cropTop = $cropData['cropTop'] ?? 0;
        $cropWidth = $cropData['cropWidth'] ?? $needParams[0];
        $cropHeight = $cropData['cropHeight'] ?? $needParams[1];

        if ($origParams[0] >= ($cropWidth * $cropScale) && $origParams[1] >= ($cropHeight * $cropScale)) {
            $src_x = ($cropLeft / $cropScale);
            $src_y = ($cropTop / $cropScale);

            if ($cropScale == 1) {

            } else {
                if ($origParams[0] < ($cropWidth * $cropScale)) {
                    $finalWidth = $origParams[0];
                    $finalHeight = $cropHeight;
                } else if ($origParams[1] < ($cropHeight * $cropScale)) {
                    $finalWidth = $cropWidth;
                    $finalHeight = $origParams[1];
                }
            }

            $origWidth = ($cropWidth / $cropScale);
            $origHeight = ($cropHeight / $cropScale);
        }

        return [$finalWidth, $finalHeight, $origWidth, $origHeight, $src_x, $src_y];
    }

    /**
     * Вычисление параметров при обрезке
     * @param array $needParams
     * @param array $origParams
     * @param bool $manualCrop Ручная обрезка, при выборе с модального кона
     * @return array
     */
    private static function getCropParams(array $needParams, array $origParams, array $cropData = []): array
    {
//        list($finalWidth, $finalHeight,
//            $origWidth, $origHeight,
//            $src_x, $src_y) = self::standardCrop($needParams, $origParams);

        list($finalWidth, $finalHeight,
            $origWidth, $origHeight,
            $src_x, $src_y) = self::advancedCrop($needParams, $origParams);

        if (!empty($cropData)) {
            list($finalWidth, $finalHeight,
                $origWidth, $origHeight,
                $src_x, $src_y) = self::manualCrop($needParams, $origParams, $cropData);
        }

        return [
            $finalWidth, $finalHeight,
            $origWidth, $origHeight,
            $src_x, $src_y,
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
    public static function getExtType($mimeType)
    {
        $names = explode('/', $mimeType);
        $type = end($names);

        if (in_array($type, [self::FILE_EXT_JPEG, self::FILE_EXT_JPG])) {
            $type = self::FILE_EXT_JPG;
        }

        return $type;
    }

    /**
     * Получаем массив параметров из родительской модели для обрезки изображения
     * @param UnitActive $model
     * @param string $attr
     * @return array
     */
    public static function getSizeDataByModelAttr(UnitActive $model, string $attr)
    {
        $data = [];
        if (!isset($model->unitData()['relations'][$attr])
            || !isset($model->unitData()['relations'][$attr]['data'])) {
            return [];
        }

        $params = $model->unitData()['relations'][$attr]['data'];
        if (count($params) == 3) {
            $hasError = false;
            foreach ($params as $index => $values) {
                if (isset($values[0]) && isset($values[1])) {
                    if (!is_int($values[0]) || !is_int($values[1])) {
                        $hasError = true;
                        break;
                    }

                    $data[$index] = [$values[0], $values[1]];
                } else {
                    $hasError = true;
                    break;
                }
            }

            if (!$hasError) {
                $keys = [Image::NAME_SIZE_MINI, Image::NAME_SIZE_NORMAL, Image::NAME_SIZE_FULL];
                $data = array_combine($keys, $data);
            }
        }

        return $data;
    }

    public function __construct(Image $model, array $sizeData = [], array $manualCropData = [])
    {
        $this->fileData = $model->fileData;
        $this->model_name = $model->model_name;
        $this->model_id = $model->model_id;
        $this->model_attr = $model->model_attr;
        $this->id = $model->id;

        $this->setImageData();
        $this->setSizeData($sizeData);
        $this->setManualCropData($manualCropData);
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

    private function setSizeData(array $sizeData = [])
    {
        $this->sizeData = [
            Image::NAME_SIZE_MINI => [
                $sizeData[Image::NAME_SIZE_MINI][0] ?? 40,
                $sizeData[Image::NAME_SIZE_MINI][1] ?? 40
            ],
            Image::NAME_SIZE_NORMAL => [
                $sizeData[Image::NAME_SIZE_NORMAL][0] ?? 150,
                $sizeData[Image::NAME_SIZE_NORMAL][1] ?? 150
            ],
            Image::NAME_SIZE_FULL => [
                $sizeData[Image::NAME_SIZE_FULL][0] ?? 360,
                $sizeData[Image::NAME_SIZE_FULL][1] ?? 360
            ],
        ];
    }

    private function setManualCropData($manualCropData)
    {
        $this->manualCropData = $manualCropData;
    }

    protected function getManualCropData()
    {
        return $this->manualCropData;
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
            || empty($this->model_attr) || empty($this->id)
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

        if (!$image = $this->imageCreateFrom()) {
            return false;
        }

        $dirPath = WEB_PATH . '/upload/' . $this->model_name . '/' . $this->model_attr . '/';
        $this->checkDirExists($dirPath);
        $zPath = zROOT . $dirPath;
        $currentTime = time();

        foreach ($this->getDataSizes() as $sizeType => $dataSize) {
            $path = $zPath . $sizeType . '/';

            list($width, $height, $origWidth, $origHeight, $src_x, $src_y) = self::getCropParams($dataSize,
                [$this->file['width'], $this->file['height']], $this->getManualCropData());

            $this->fileName = $this->id . '_' . $currentTime . '.' . $this->file['extension'];

            $imageTemp = imagecreatetruecolor($width, $height);

            if (!$this->useWaterMark) {
                imagealphablending($imageTemp, false);
                imagesavealpha($imageTemp, true);
            }

            imagecopyresampled($imageTemp, $image, 0, 0, $src_x, $src_y, $width, $height, $origWidth, $origHeight);

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

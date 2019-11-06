<?php

namespace tfl\handlers\html;

use app\models\Image;
use tfl\handlers\upload\UploadHandler;
use tfl\units\UnitActive;
use tfl\utils\tHTML;
use tfl\utils\tHtmlTags;
use tfl\utils\tString;

final class BbTags
{
    private $model;
    private $attr;

    private static $simpleTags = [
        'b', 'i', 'u', 's',
        'center',
        'sup', 'sub',
        'q', 'blockquote', 'spoiler', 'p',
    ];

    public static function replaceTags(string $value = null)
    {
        if (!$value) {
            return '';
        }

        foreach (self::$simpleTags as $index => $tag) {
            $value = preg_replace('!\[' . $tag . '](.*?)\[/' . $tag . ']!msi', '<' . $tag . '>\\1</' . $tag . '>', $value);
        }

        $value = preg_replace('!\[hr]!msi', '<hr/>', $value);

        $value = preg_replace('!\[color=(.*?)](.*?)\[/color]!msi', '<span style="color:$1">$2</span>', $value);
        $value = preg_replace('!\[size=(.*?)](.*?)\[/size]!msi', '<span style="font-size:$1%">$2</span>', $value);

        $value = preg_replace_callback("!\[thumb](.*?)\[/thumb]!msi", function ($matches) {
	        $pathNames = explode(WEB_SEP, $matches[1]);

	        $urlFull = ROOT . UploadHandler::SAVE_PATH . implode(WEB_SEP, $pathNames);

            $index = (count($pathNames) - 2);

            $pathNames[$index] = Image::NAME_SIZE_NORMAL;
	        $urlNormal = ROOT . UploadHandler::SAVE_PATH . implode(WEB_SEP, $pathNames);

            $image = tHtmlTags::startTag('a', [
                'href' => $urlFull,
                'target' => '_blank',
                'class' => 'tImage',
            ]);
            $image .= tHtmlTags::renderClosedTag('img', [
                'src' => $urlNormal
            ]);
            $image .= tHtmlTags::endTag('a');

            return $image;
        }, $value);

        return $value;
    }

    /**
     * Превращаем [b]текст[/b] в "текст"
     * @param string|null $text
     * @return mixed|string|string[]|null
     */
    public static function clearBBTags(string $text = null)
    {
        if (!$text) {
            return $text;
        }

        $text = preg_replace('/\s*(\r\n|\n\r|\n)\s*/', ' ', $text);
        $text = str_replace('<br/>', ' ', $text);
        $text = str_replace('<br>', ' ', $text);

        $text = strip_tags($text, '<br/><br>');

        //Тэги, которые нужно удалить из текста
        $listRemoveTags = [
            'thumb',
            'image',
            'img',
        ];
        foreach ($listRemoveTags as $oneTag) {
            $text = preg_replace('!\[' . $oneTag . '](.*?)\[/' . $oneTag . ']!msi', '', $text);
        }

        //Замена дубликатов
        $text = preg_replace('|\s+|', ' ', $text);
        //Замена [b]текст[/b]
        $text = preg_replace('#\[/?[^]]+]#', '', $text);
        //Замена "плохих" символов
        $text = str_replace(chr(194) . chr(160), ' ', $text);

        return trim($text);
    }

    public function __construct(UnitActive $model, string $attr)
    {
        $this->model = $model;
        $this->attr = $attr;
        $this->field = $model->getModelName() . '[' . $attr . ']';
    }

    public function render()
    {
        $t = tHtmlTags::startTag('div', [
            'class' => [
                'bbtags-field',
                'bbtags-' . $this->model->getModelNameLower() . '-field',
            ],
        ]);

        foreach (self::$simpleTags as $index => $tag) {
            $t .= tHTML::inputActionButton('', '', [], [
                'class' => ['insert-tag', 'tag', 'tag-' . $tag],
                'title' => $tag,

                'data-tag' => $tag,
                'data-field' => $this->field,
            ]);
        }

        $t .= tHtmlTags::endTag();

        return $t;
    }
}

<?php

namespace tfl\utils;

class tHtmlTags
{
    public static function render(string $tag, string $body, $attrList = null)
    {
        $t = self::startTag($tag, $attrList);
        $t .= $body;
        $t .= self::endTag($tag);

        return $t;
    }

    public static function renderClosedTag(string $tag, $attrList = null, $text = null)
    {
        $t = self::startTag($tag, $attrList, false, $text);
        $t .= '/>';

        return $t;
    }

    public static function startTag(string $tag = 'div', $attrList = null, $close = true, $text = null)
    {
        $t = '<' . $tag;

        if (!empty($attrList)) {
            if (is_array($attrList)) {
                foreach ($attrList as $attrName => $attrValue) {
                    if (empty($attrValue)) {
                        continue;
                    }
                    $valueString = is_array($attrValue) ? implode(' ', $attrValue) : $attrValue;
                    $t .= ' ' . $attrName . '="' . $valueString . '"';
                }
            } else {
                $t .= ' ' . $attrList;
            }
        }

        if ($text) {
            $t .= $text;
        }

        if ($close) {
            $t .= '>';
        }

        return $t;
    }

    public static function endTag(string $tag = 'div')
    {
        return '</' . $tag . '>';
    }
}
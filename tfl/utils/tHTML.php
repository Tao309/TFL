<?php

namespace tfl\utils;

class tHTML
{
    public static function decodeOptions(array $options = [])
    {
        $input = '';
        foreach ($options as $optionIndex => $option) {
            switch ($optionIndex) {
                case 'disabled':
                case 'readonly':
                    $input .= ' ' . $optionIndex;
                    break;
                case 'class':
                    if (!is_array($option)) {
                        $option = explode(' ', $option);
                    }
                    $input .= ' class="' . implode(' ', $option) . '"';
                    break;
                default:
                    $input .= " " . $optionIndex . "='" . $option . "'";

            }
        }

        return $input;
    }

    public static function inputText(
        string $name = null,
        string $value = null,
        string $limit = null,
        array $options = []
    )
    {
        $input = '<input type="text" value="' . $value . '" name="' . $name . '"';
        if ($limit) {
            $limit = (int)$limit;
            $input .= ' maxlength="' . $limit . '"';
        }

        if (isset($options['class'])) {
            $options['class'] .= ' html-element-text';
        } else {
            $options['class'] = 'html-element-text';
        }

        $input .= self::decodeOptions($options);

        $input .= '/>';

        return $input;
    }

    public static function inputSelect(
        string $name = null,
        array $values = [],
        string $selected = null,
        array $options = []
    )
    {
        $input = '<select name="' . $name . '" class="html-element-select">';

        $input .= '<option hidden></option>';

        foreach ($values as $index => $data) {
            $createOptGroup = is_array($data);

            if ($createOptGroup) {
                if (empty($data[1])) {
                    continue;
                }

                $input .= '<optgroup label="' . $data[0] . '">';

                foreach ($data[1] as $index2 => $oneData) {
                    $input .= self::inputOneSelectField($index2, $oneData, $selected);
                }

                $input .= '</optgroup>';
            } else {
                $input .= self::inputOneSelectField($index, $data, $selected);
            }
        }

        $input .= '</select>';

        return $input;
    }

    private static function inputOneSelectField($index, $value, $selected)
    {
        $input = '<option value="' . $index . '"';

        if ($index == $selected) {
            $input .= ' selected';
        }

        $input .= '>' . $value . '</option>';

        return $input;
    }

    public static function inputLink($link, $value = null, $options = [])
    {
        $input = '<a';

        if (!tString::isUrl($link)) {
            $link = ROOT . $link;
        }

        $input .= ' href="' . $link . '"';

        if (isset($options['html'])) {
            $input .= ' ' . $options['html'];
            unset($options['html']);
        }

        $input .= self::decodeOptions($options);
        $input .= '>';
        $input .= $value;
        $input .= '</a>';

        return $input;
    }
}
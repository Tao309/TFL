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
        string $name,
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

    public static function inputHidden(string $name, string $value = null, array $options = [])
    {
        $input = '<input type="hidden" value="' . $value . '" name="' . $name . '"';
        $input .= self::decodeOptions($options);
        $input .= '/>';
        return $input;
    }

    private static $checkBoxNumber = 0;

    public static function inputCheckbox(string $name, $value = 0)
    {
        self::$checkBoxNumber += 1;
        $elementId = 'checkbox-element-' . $name . '-' . self::$checkBoxNumber;

        $t = '<div class="html-element-checkbox">';
        $checkedHidden = ($value) ? '' : 'checked';
        $t .= '<input type="checkbox" name="' . $name . '" value="0" hidden ' . $checkedHidden . '/>';
        $checked = ($value) ? 'checked' : '';
        $t .= '<input type="checkbox" name="' . $name . '" value="1" class="checkbox" id="' . $elementId . '" ' . $checked . '/>';
        $t .= '<label for="' . $elementId . '">';
        $t .= '</label>';
//        $t .= '<label class="off"></label>';
        $t .= '';
        $t .= '';
        $t .= '</div>';


        return $t;
    }

    public static function inputSelect(
        string $name,
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
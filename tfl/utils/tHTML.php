<?php

namespace tfl\utils;

use app\models\Page;
use app\models\User;

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
			$limit = tString::encodeNum($limit);
			$input .= ' maxlength="' . $limit . '"';
		}

		if (isset($options['class'])) {
			if (!is_array($options['class'])) {
				$options['class'] = [$options['class']];
			}
		} else {
			$options['class'] = [];
		}
		$options['class'][] = ' html-element-text';

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

	public static function inputFile(string $name, string $value = null, array $options = [])
	{
		$input = '<input type="file" value="' . $value . '" name="' . $name . '"';
		$input .= self::decodeOptions($options);
		$input .= '/>';
		return $input;
	}

	public static function inputTextarea(string $name,
	                                     string $value = null,
	                                     string $limit = null,
	                                     array $options = [])
	{
		if (isset($options['class'])) {
			if (!is_array($options['class'])) {
				$options['class'] = [$options['class']];
			}
		} else {
			$options['class'] = [];
		}
		$options['class'][] = 'html-element-textarea';

		$t = tHtmlTags::startTag('textarea', [
			'name' => $name,
			'id' => $name,
			'class' => $options['class'],
		]);

		$t .= $value;

		$t .= tHtmlTags::endTag('textarea');

		return $t;
	}

	public static function inputActionButton($name, $value, $elementData = null, array $options = [])
	{
		if (isset($options['class']) && !isset($options['type'])) {
			$options['class'][] = 'http-request-button';
		}

		$input = '<button';
		$input .= ' name="' . $name . '"';
		$input .= ' type="' . ($options['type'] ?? 'button') . '"';
		$input .= self::decodeOptions($options);
		if ($elementData) {
			$input .= "" . $elementData;
		}
		$input .= '>';
		$input .= $value;
		$input .= '</button>';

		return $input;
	}

	public static function inputSubmitButton($name, $value, array $options = [])
	{
		$options['class'] = ['html-element', 'html-button', 'size-large'];
		$options['type'] = 'submit';
		return self::inputActionButton($name, $value, null, $options);
	}

	private static $checkBoxNumber = 0;

	private static function renderCheckbox(string $name, $value = 0, array $options = [], $class = 'html-element-checkbox')
	{
		self::$checkBoxNumber += 1;
		$elementId = 'checkbox-element-' . $name . '-' . self::$checkBoxNumber;

		$t = '<div class="' . $class . '">';
		$checkedHidden = ($value) ? '' : 'checked';
		$t .= '<input type="checkbox" name="' . $name . '" value="0" hidden ' . $checkedHidden . '/>';
		$checked = ($value) ? 'checked' : '';
		$t .= '<input ' . self::decodeOptions($options) . ' type="checkbox" name="' . $name . '" value="1" class="checkbox" id="' . $elementId . '" ' . $checked . '/>';
		$t .= '<label for="' . $elementId . '"></label>';
		$t .= '';
		$t .= '';
		$t .= '</div>';

		return $t;
	}

	public static function inputCheckbox(string $name, $value = 0, array $options = [])
	{
		return self::renderCheckbox($name, $value, $options);
	}

	public static function inputValidCheckbox(string $name, $value = 0, array $options = [])
	{
		return self::renderCheckbox($name, $value, $options, 'html-element-valid-checkbox');
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

	/**
	 * @param array $sourceArray Массив со значениями
	 * @param array $namesArray Массив с переводом главных ключей и структурой для показа
	 * @param string $inputName Начальное название элемента, f.e. Role[users]
	 * @return string
	 */
	private static function renderArray(array $valuesArray, array $namesArray, string $inputName = null): string
	{
		$t = tHtmlTags::startTag('div', ['class' => 'array-field']);
		/// as 'models' => [User::class => 'Users', Page::class => 'Pages']
		foreach ($namesArray as $index => $data) {
			$t .= tHtmlTags::render('div', ucfirst($index), ['class' => 'header']);
			foreach ($data as $nameIndex => $name) {
				if (is_array($name)) {

				} else {
					$valid = (isset($valuesArray[$index][$nameIndex]) && $valuesArray[$index][$nameIndex] > 0);
					$value = $valid ? true : false;

					if ($inputName) {
						$el = self::inputValidCheckbox($inputName . '[' . $index . '][' . $nameIndex . ']', $value);
					} else {
						$classes = ['html-icon-button', 'view-icon'];
						if ($valid) {
							$classes[] = 'icon-valid';
						} else {
							$classes[] = 'icon-not-valid';
						}

						$el = tHtmlTags::render('span', '', ['class' => $classes]);
					}

					$title = tHtmlTags::render('span', $name . ':', ['class' => 'title']);
					$t .= tHtmlTags::render('div', $title . $el, ['class' => 'row']);
				}
			}
		}
		$t .= tHtmlTags::endTag();

		return $t;
	}

	public static function editArray(string $inputName, array $valuesArray, array $namesArray): string
	{
		return self::renderArray($valuesArray, $namesArray, $inputName);
	}

	public static function viewArray(array $valuesArray, array $namesArray): string
	{
		return self::renderArray($valuesArray, $namesArray);
	}

	public static function addParamsToLink($link, $options = [])
	{
		if (!empty($options)) {
			$link .= '&' . http_build_query($options);
		}

		return $link;
	}
}
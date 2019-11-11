<?php

namespace tfl\utils;

use tfl\builders\RequestBuilder;
use tfl\units\UnitActive;
use tfl\units\UnitOption;

class tHtmlForm
{
    const INDEX_TYPE = 'section';
    const INDEX_PAGE = 'route';
    const INDEX_PAGE_SECTION = 'routeType';
    const INDEX_PAGE_SUB_SECTION = 'routeSubType';

    const NAME_METHOD = '_method';

    public static function loginForm()
    {
        $data = ['section', 'auth', 'login'];

        $elements = [
            [
                'type' => 'text',
                'name' => 'User[login]',
                'label' => 'Login (or Email):',
                'length' => 20,
            ],
            [
                'type' => 'text',
                'name' => 'User[password]',
                'length' => 20,
                'label' => 'Password',
            ],
            [
                'type' => 'submit',
                'name' => 'submit',
                'label' => 'Login',
                'align' => 'center',
            ],
        ];

	    return self::simpleForm(tRoute::SECTION_ROUTE_CREATE, $data, $elements);
    }

    public static function registerForm()
    {
        $data = ['section', 'auth', 'register'];

        $elements = [
            [
                'type' => 'text',
                'name' => 'User[login]',
                'label' => 'Login',
                'length' => 20,
                'options' => [

                ],
            ],
            [
                'type' => 'text',
                'name' => 'User[email]',
                'length' => 60,
                'label' => 'E-mail',
            ],
            [
                'type' => 'text',
                'name' => 'User[password]',
                'length' => 20,
                'label' => 'Password',
            ],
            [
                'type' => 'submit',
                'name' => 'submit',
                'label' => 'Register',
                'align' => 'center',
            ],
        ];

	    return self::simpleForm(tRoute::SECTION_ROUTE_CREATE, $data, $elements);
    }

    public static function requestForm()
    {
        $data = ['section', 'auth', 'request'];

        $elements = [
            [
                'type' => 'text',
                'name' => 'User[email]',
                'length' => 60,
                'label' => 'E-mail',
            ],
            [
                'type' => 'submit',
                'name' => 'submit',
                'label' => 'Send',
                'align' => 'center',
            ],
        ];

	    return self::simpleForm(tRoute::SECTION_ROUTE_CREATE, $data, $elements);
    }

	public static function simpleForm($type, $data = [], $elements = null)
    {
        $classNames = [];
        $classNames[] = 'http-request-form';
        $classNames[] = 'html-element';
        $classNames[] = 'html-element-form';

	    $formId = implode('-', $data);
	    $formId = str_replace('/', '-', $formId);

        $form = '<form method="' . RequestBuilder::METHOD_POST . '" enctype="multipart/form-data" ';
        $form .= 'class="' . implode(' ', $classNames) . '" ';
	    $form .= 'id="' . $formId . '" ';
	    $form .= self::generateElementData($data, self::getMethodByType($type, true));
        $form .= '>';
	    $form .= tHTML::inputHidden(self::NAME_METHOD, self::getMethodByType($type, true));

        if (is_array($elements)) {
            $form .= '<ul class="html-element-ul">';
            $form .= self::renderElements($elements);
            $form .= '</ul>';
        } else {
            $form .= $elements;
        }

        $form .= '</form>';

        return $form;
    }

	/**
	 * Получаем метод запросы (POST, DELETE, GET) по типу запроса (UPDATE, CREATE, DELETE)
	 * @param string $type
	 * @param bool $forceDirectMethod Получить метод напрямую, без замены
	 * @return string
	 */
	public static function getMethodByType(string $type, $forceDirectMethod = false): string
	{
		switch ($type) {
			case tRoute::SECTION_ROUTE_ADD:
				return RequestBuilder::METHOD_POST;
				break;
			case tRoute::SECTION_ROUTE_UPDATE:
				if ($forceDirectMethod) {
					return RequestBuilder::METHOD_PUT;
				}

				return RequestBuilder::METHOD_POST;
				break;
			case tRoute::SECTION_ROUTE_DELETE:
				if ($forceDirectMethod) {
					return RequestBuilder::METHOD_DELETE;
				}

				return RequestBuilder::METHOD_POST;
				break;
			default:
				return RequestBuilder::METHOD_GET;
		}
	}

	/**
	 * Создание основных данных запроса
	 * @param string $type
	 * @param UnitActive $model
	 * @return array
	 */
	public static function generateRestData(string $type, UnitActive $model): array
	{
		$t = [];
		$t[] = \TFL::source()->section->getRouteDirection();

		if ($model instanceof UnitOption) {
			$t[] = 'option';
			$t[] = lcfirst($model->getModelName());
		} else {
			$t[] = $model->getModelNameLower();

			if ($type == tRoute::SECTION_ROUTE_ADD) {
				$t[] = tRoute::SECTION_ROUTE_ADD;
			} else if (!$model->isNewModel()) {
				$t[] = $model->id;
			}
		}

		return $t;
	}

	/**
	 * Создание данных для кнопок под REST запросы
	 * @param string $type
	 * @param UnitActive $model
	 * @return string
	 */
	public static function generateRestButtonData(string $type, UnitActive $model): string
	{
		$data = self::generateRestData($type, $model);

		return self::generateElementData($data, self::getMethodByType($type));
	}

	/**
	 * Простая генерация данных на вывод для элемента
	 * @param array $data
	 * @param null $method
	 * @return string
	 */
	public static function generateElementData(array $data, $method = null): string
    {
	    $t = [];

	    $t[] = 'data-' . self::INDEX_TYPE . '="' . $data[0] . '"';
	    $t[] = 'data-' . self::INDEX_PAGE . '="' . mb_strtolower($data[1]) . '"';
	    if (isset($data[2])) {
		    $t[] = 'data-' . self::INDEX_PAGE_SECTION . '="' . $data[2] . '"';
        }

	    if (!empty($method)) {
		    $method = (in_array(mb_strtolower($method), [RequestBuilder::METHOD_POST, RequestBuilder::METHOD_PUT]))
                ? RequestBuilder::METHOD_POST : RequestBuilder::METHOD_GET;
		    $t[] = 'data-method="' . $method . '"';
        }

	    return ' ' . implode(' ', $t);
    }

    public static function generateDataParams($data = [], $view = false)
    {
        if (empty($data)) {
            return null;
        }
        if ($view) {
            return json_encode($data);
        }

        return " data-params='" . json_encode($data) . "'";
    }

    private static function renderElements($elements)
    {
        $form = '';

        foreach ($elements as $element) {
            $form .= self::renderElement($element);
        }

        return $form;
    }

    private static function renderElement($element)
    {
        $form = '';

        $value = $element['value'] ?? null;
        $values = $element['values'] ?? [];

        $classNames = [];
        $classNames[] = 'html-element-label';

        if (in_array($element['type'], [
            'checkbox',
            'text',
        ])) {
            if (isset($element['align']) && $element['align'] == 'block') {
                $classNames[] = 'element-display-block';
            }
            if (isset($element['option']) && isset($element['option']['class'])) {
                $classNames[] = $element['option']['class'];
            }
        }

        if (in_array($element['type'], [
            'select',
            'checkbox',
            'text',
        ])) {
            $form .= '<li class="html-element-li">';
            $form .= '<label class="' . implode(' ', $classNames) . '">';
            $form .= $element['label'] ?? $element['name'];
            $form .= '</label>';
        }

        switch ($element['type']) {
            case 'select':
                $form .= tHTML::inputSelect($element['name'], $values, $value);
                break;
            case 'hiddenText':
            case 'hidden':
                $form .= '<input type="hidden" name="' . $element['name'] . '" class="html-element html-element-hidden" value="' . $value . '"';
                $form .= '/>';
                break;
            case 'checkbox':
                $form .= '<input type="checkbox" name="' . $element['name'] . '" class="html-element html-element-text" value="1"';
                if ($value) {
                    $form .= ' checked';
                }
                $form .= '/>';
                break;
            case 'text':
                $form .= '<input type="text" name="' . $element['name'] . '" class="html-element html-element-text" value="' . $value . '"';
                if (isset($element['length']) && $element['length'] > 0) {
                    $form .= ' maxlength="' . $element['length'] . '"';
                }
                $form .= '/>';
                break;
            case 'submit':
            case 'button':
                $classNames = [];
                $classNames[] = 'html-element-li';
                if ($element['type'] == 'submit') {
                    $classNames[] = 'type-submit';
                    if (isset($element['align'])) {
                        switch ($element['align']) {
                            case 'center':
                                $classNames[] = 'type-submit-center';
                                break;
                            case 'right':
                                $classNames[] = 'type-submit-right';
                                break;
                            case 'bytext':
                                $classNames[] = 'type-submit-bytext';
                                break;
                        }
                    }
                }

                $form .= '<li class="' . implode(' ', $classNames) . '">';
                $form .= '<button';
                if ($element['type'] == 'submit') {
                    $form .= ' type="submit"';
                }
                $form .= ' name="' . $element['name'] . '"';
                $form .= ' class="html-element html-button"';
                $form .= '>';
                $form .= $element['label'] ?? $element['name'];
                $form .= '</button>';
                $form .= '</li>';
                break;
        }

        return $form;
    }
}
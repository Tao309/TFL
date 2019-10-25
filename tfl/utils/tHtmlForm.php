<?php

namespace tfl\utils;

use tfl\builders\RequestBuilder;

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

        return self::simpleForm($data, $elements, [], RequestBuilder::METHOD_POST);
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

        return self::simpleForm($data, $elements, [], RequestBuilder::METHOD_POST);
    }

    public static function requestForm()
    {
        $data = ['section', 'auth', 'erquest'];

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

        return self::simpleForm($data, $elements, [], RequestBuilder::METHOD_POST);
    }

    public static function simpleForm($data = [], $elements = null, $options = [], $method = null)
    {
        $classNames = [];
        $classNames[] = 'http-request-form';
        $classNames[] = 'html-element';
        $classNames[] = 'html-element-form';

        $form = '<form method="' . RequestBuilder::METHOD_POST . '" enctype="multipart/form-data" ';
        $form .= 'class="' . implode(' ', $classNames) . '" ';
        $form .= 'id="' . implode('-', $data) . '" ';
        $form .= self::generateElementData($data, $method);
        $form .= '>';

        if (is_array($elements)) {
            $form .= '<ul class="html-element-ul">';

            $elements[] = [
                'type' => 'hidden',
                'value' => $method,
            ];

            $form .= self::renderElements($elements);
            $form .= '</ul>';
        } else {
            $form .= tHTML::inputHidden(self::NAME_METHOD, $method);
            $form .= $elements;
        }

        $form .= '</form>';

        return $form;
    }

    public static function generateElementData($data = [], $type = null, $options = [])
    {
        $input = [];

        if (isset($data[0])) {
            $input[] = 'data-' . self::INDEX_TYPE . '="' . $data[0] . '"';
            if (isset($data[1])) {
                $input[] = 'data-' . self::INDEX_PAGE . '="' . mb_strtolower($data[1]) . '"';
                if (isset($data[2])) {
                    $input[] = 'data-' . self::INDEX_PAGE_SECTION . '="' . $data[2] . '"';
                }
            }
        }

        if ($type) {
            $type = (in_array(mb_strtolower($type), [RequestBuilder::METHOD_POST, RequestBuilder::METHOD_PUT]))
                ? RequestBuilder::METHOD_POST : RequestBuilder::METHOD_GET;
            $input[] = 'data-method="' . $type . '"';
        }

        if (!empty($options)) {
            $input[] = tHTML::decodeOptions($options);
        }

        return ' ' . implode(' ', $input);
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
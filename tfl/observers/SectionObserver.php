<?php

namespace tfl\observers;

use tfl\builders\MenuBuilder;
use tfl\builders\RequestBuilder;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;

trait SectionObserver
{
    protected function replaceConstants(string $type)
    {
        $vars = [];

        if ($type == self::TYPE_HEADER) {
            $vars['header'] = [
                'lang' => $this->getReplacedConstantsLang(),
                'metatags' => $this->getReplacedConstantsMetatags(),
            ];
        }

        $vars['DOM'] = [
            'CSS' => $this->getReplacedConstants('css', $type),
            'JS' => $this->getReplacedConstants('js', $type),
        ];

        $vars['site'] = [
            'version' => \TFL::source()->getVersion(),
            'year_current' => date('Y'),
            'year_created' => '2019',
        ];


        if ($type == self::TYPE_BODY) {
            $assignVars = $this->getAssignVars();
            if (!empty($assignVars)) {
                $vars['page'] = $assignVars;
            }
        }

        $this->replaceMenuFields();

        $this->replaceVars($vars);
        $this->replaceEval();

        return $this->content;
    }

    private function replaceVars(array $vars): void
    {
        $vars = $this->generateSectionConstants($vars);

        foreach ($vars as $indexName => $valueName) {
            $this->content = preg_replace('/{' . $indexName . '}/i', $valueName, $this->content);
        }
    }

    private function replaceEval(): void
    {
        $this->content = preg_replace_callback('/{{(.*?)}}/si', function ($matches) {
            if (isset($matches[1])) {
                return $this->replaceComputeVars(trim($matches[1]));
            }

            return '';
        }, $this->content);
    }

    private function replaceComputeVars($match)
    {
        $match = $this->replaceGlobalComputeVars($match);

        $computeVars = $this->getComputeVars();
        if (!empty($computeVars) && is_array($computeVars)) {
            foreach ($computeVars as $index => $value) {
                $match = preg_replace('/{' . $index . '}/i', $value, $match);
            }
        }

        $match = preg_replace('/{(.*?)}/i', '', $match);

        return eval($match);
    }

    private function replaceGlobalComputeVars($match)
    {
        foreach ($this->generateSectionConstants($this->getGlobalComputeConstants()) as $index => $value) {
            if (is_string($value)) {
//                $value = '"' . $value . '"';
                $value = $value;
            } else {
                if ($value === true) {
                    $value = 1;
                } elseif ($value === false) {
                    $value = 0;
                }
            }

            $match = preg_replace('/{' . $index . '}/i', $value, $match);
        }

        return $match;
    }

    private function getGlobalComputeConstants()
    {
        $vars = [
            'ROOT' => ROOT,
            'user' => [
                'isGuest' => \TFL::source()->session->isGuest(),
                'isUser' => \TFL::source()->session->isUser(),
                'model' => null,
                'userStatus' => 0,
                'avatar' => '',
            ],
        ];
        if ($vars['user']['isUser']) {
            $user = \TFL::source()->session->currentUser();
            $vars['user']['model'] = (string)$user;
            $vars['user']['userStatus'] = $user->status;
            $vars['user']['avatar'] = \TFL::source()->session->currentUser()->getAvatar();
        }

        $buttonLogin = null;
        $buttonRegister = null;
        $buttonRequestPassword = null;

        $buttonToAdmin = null;
        $buttonExit = null;
        if ($vars['user']['isUser']) {
            $buttonToAdmin = tHTML::inputLink(ROOT . 'admin/', 'Админ-центр', [
                'class' => ['html-button', 'html-button-auth', 'html-button-admin']
            ]);

            $htmlData = tHtmlForm::generateElementData([
	            'section', 'auth', 'exit',
            ], RequestBuilder::METHOD_POST, [
                'class' => ['http-request-button', 'html-button', 'html-button-auth', 'html-button-exit']
            ]);
            $buttonExit = tHTML::inputLink(ROOT . 'exit/', 'Выход', [
                'html' => $htmlData,
            ]);
        } else {
            $htmlData = tHtmlForm::generateElementData([
	            'section', 'window', 'login',
            ], RequestBuilder::METHOD_GET, [
                'class' => ['http-request-button', 'html-button', 'html-button-auth', 'html-button-login']
            ]);
            $buttonLogin = tHTML::inputLink(ROOT . 'login/', 'Вход', [
                'html' => $htmlData,
            ]);

            $htmlData = tHtmlForm::generateElementData([
	            'section', 'window', 'register',
            ], RequestBuilder::METHOD_GET, [
                'class' => ['http-request-button', 'html-button', 'html-button-auth', 'html-button-register']
            ]);
            $buttonRegister = tHTML::inputLink(ROOT . 'register/', 'Регистрация', [
                'html' => $htmlData,
            ]);

            $htmlData = tHtmlForm::generateElementData([
	            'section', 'window', 'requestpassword',
            ], RequestBuilder::METHOD_GET, [
                'class' => ['http-request-button', 'html-button', 'html-button-auth', 'html-button-requestpassword']
            ]);
            $buttonRequestPassword = tHTML::inputLink(ROOT . 'requestpassword/', 'Вернуть пароль', [
                'html' => $htmlData,
            ]);
        }
        $vars['button'] = [
            'login' => $buttonLogin,
            'register' => $buttonRegister,
            'requestpassword' => $buttonRequestPassword,
            'toAdmin' => $buttonToAdmin,
            'exit' => $buttonExit,
        ];

        return $vars;
    }

    private function generateSectionConstants(array $array): array
    {
        $replaceArray = [];

        foreach ($array as $index => $values) {
            if (is_array($array[$index])) {
                foreach ($values as $indexValue => $value) {
                    $replaceArray[$index . ':' . $indexValue] = $value;
                }
            } else {
                $replaceArray[$index] = $values;
            }
        }

        return $replaceArray;
    }

    private function replaceMenuFields()
    {
        $this->content = preg_replace_callback('!{menu:(.*?)}!msi', function ($match) {
            if (!isset($match[1])) {
                return '';
            }

            $menuName = ucfirst($match[1]);

            $className = 'app\\models\\menu\\Menu' . $menuName;
            if (!class_exists($className)) {
                return '';
            }

            /**
             * @var MenuBuilder $model
             */
            $model = new $className();

            return $model->render();
        }, $this->content);
    }
}

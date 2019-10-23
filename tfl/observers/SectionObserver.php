<?php

namespace tfl\observers;

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
            'CSS' => $this->getReplacedConstantsCss($type),
            'JS' => $this->getReplacedConstantsJs($type),
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

        $match = preg_replace('/{(.*?)}/i', 'null', $match);

        return eval($match);
    }

    private function replaceGlobalComputeVars($match)
    {
        $vars = [
            'user' => [
                'isGuest' => \TFL::source()->session->isGuest(),
                'isUser' => \TFL::source()->session->isUser(),
                'model' => \TFL::source()->session->currentUser(),
            ],
        ];

        foreach ($this->generateSectionConstants($vars) as $index => $value) {
            if (is_string($value)) {
                $value = '"' . $value . '"';
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
}

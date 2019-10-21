<?php

namespace tfl\observers;

trait SectionObserver
{
    protected function replaceConstants(string $type)
    {
        $vars = [];
        $vars['page'] = [
            'metatags' => 'Metatags Field',
        ];

        $vars['DOM'] = [
            'CSS' => 'CSS Field',
            'JS' => 'JS Field',
        ];

        $vars['site'] = [
            'version' => \TFL::source()->getVersion(),
            'year_current' => date('Y'),
            'year_created' => '2019',
        ];

        $this->replaceVars($vars);

        return $this->content;
    }

    private function replaceVars(array $vars): void
    {
        $vars = $this->generateSectionContants($vars);

        foreach ($vars as $indexName => $valueName) {
            $this->content = preg_replace('/{' . $indexName . '}/i', $valueName, $this->content);
        }
    }

    private function generateSectionContants(array $array): array
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

<?php

namespace tfl\builders;

use tfl\units\Unit;

/**
 * elements:
 * * title          Название элемента
 * * rule           Правило, по которому отображать
 * * link           Ссылка элемента
 * * linkBlank      Открывать ссылку в новом окне
 * * class          Добавлять класс (может быть массив) к элементу
 */

/**
 * Class MenuBuilder
 * @package tfl\builders
 */
abstract class MenuBuilder extends Unit
{
    const TYPE_UL = 'ul';
    const TYPE_DIV = 'div';

    const HEAD_DIV = 'div';
    const HEAD_H1 = 'h1';
    const HEAD_H2 = 'h2';
    const HEAD_H3 = 'h3';
    const HEAD_H4 = 'h4';

    private $defaultHead = self::HEAD_H1;
    private $defaultType = self::TYPE_UL;

    /**
     * @var array
     */
    private $elements;
    /**
     * @var string
     */
    private $currentUrl;

    abstract protected function elements();

    public function __construct()
    {
        parent::__construct();

        $this->elements = $this->elements();
        $this->currentUrl = \TFL::source()->path->getCurrentUrl();
    }

    public function render()
    {
        return $this->renderMenu();
    }

    private function renderMenu()
    {
        $t = '';
        $t .= '<' . $this->defaultType;
        $t .= ' id="' . $this->getMenuId() . '"';
        $t .= ' class="html-element-menu"';
        $t .= '>';
        foreach ($this->elements as $index => $row) {
            $t .= $this->renderRow($row);
        }
        $t .= '</' . $this->defaultType . '>';

        return $t;
    }

    private function getMenuId()
    {
        return 'menu-' . lcfirst(preg_replace('/^Menu/i', '', $this->getModelName()));
    }

    private function renderRow(array $data): string
    {
        if (isset($data['rule']) && !$data['rule']) {
            return '';
        }

        $rowTag = 'li';
        if ($this->defaultType == self::TYPE_DIV) {
            $rowTag = 'div';
        }

        $row = '';
        $row .= '<' . $rowTag . ' class="html-element-menu-li' . $this->getRowClassList($data) . '">';

        if (isset($data['link'])) {
            $row .= '<a href="' . ROOT . $data['link'] . '" class="html-element-menu-li-link"';
            if (isset($data['linkBlank']) && $data['linkBlank']) {
                $row .= ' target="_blank"';
            }
            $row .= '>';
        }

        $row .= $data['title'];

        if (isset($data['link'])) {
            $row .= '</a>';
        }

        if (isset($data['list']) && is_array($data['list']) && count($data['list'])) {
            $row .= '<' . $this->defaultType . ' class="html-element-menu-child">';
            foreach ($data['list'] as $listIndex => $listData) {
                $row .= $this->renderRow($listData);
            }
            $row .= '</' . $this->defaultType . '>';
        }

        $row .= '</' . $rowTag . '>';

        return $row;
    }

    private function getRowClassList(array $data): string
    {
        $class = [];
        if (isset($data['class'])) {
            if (is_array($data['class'])) {
                $class += $data['class'];
            } else {
                $class[] = trim($data['class']);
            }
        }

        if (isset($data['link'])) {
            //Проверку текущая ли ссылка открыта
            if (
                $data['link'] != 'admin/'
                && $data['link'] != ''
                && (
                    $this->currentUrl == $data['link']
                    || preg_match('!^' . $data['link'] . '!si', $this->currentUrl)
                )
            ) {
                $class[] = 'current-link';
            }
        }

        return (!empty($class)) ? ' ' . implode('', $class) : '';
    }
}
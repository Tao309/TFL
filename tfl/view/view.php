<?php

namespace tfl\view;

use tfl\builders\TemplateBuilder;
use tfl\units\Unit;
use tfl\utils\tHTML;

class View
{
    const TYPE_VIEW_DETAILS = 'details';
    const TYPE_VIEW_EDIT = 'edit';
//    const TYPE_VIEW_DELETE = 'delete';
    const TYPE_VIEW_ADD = 'add';
    const TYPE_VIEW_LIST = 'list';

    /**
     * @var $tplBuilder TemplateBuilder
     */
    private $tplBuilder;
    /**
     * @var $dependModel Unit
     */
    private $dependModel;

    public function __construct(TemplateBuilder $tplBuilder)
    {
        $this->tplBuilder = $tplBuilder;
        $this->dependModel = $tplBuilder->getDependModel();
    }

    public function render(): string
    {
        $t = '<div id="view-unit">';

        $t .= $this->viewHeader();
        $t .= $this->viewBody();
        $t .= $this->viewFooter();

        $t .= '</div>';

        return $t;
    }

    private function viewHeader(): string
    {
        $t = '<div class="view-header">';
        $t .= '';
        $t .= $this->tplBuilder->viewTitle();
        $t .= '';
        $t .= '</div>';
        return $t;
    }

    private function viewBody(): string
    {
        $t = '<div class="view-body type-' . $this->tplBuilder->geViewType() . '">';
        $t .= '';

        foreach ($this->tplBuilder->viewData() as $attr => $data) {
            $t .= $this->viewRow($attr, $data);
        }

        $t .= '';
        $t .= '</div>';
        return $t;
    }

    private function viewFooter(): string
    {
        return '';
    }

    private function viewRow(string $attr, array $data): string
    {
        $t = '<div class="view-row type-' . $data['type'] . '">';

        if ($data['type'] == TemplateBuilder::VIEW_TYPE_HEADER) {
            $t .= '<div class="row-title">';
            $t .= $data['title'];
            $t .= '</div>';
        } else {
            $t .= '<div class="row-title">';
            $t .= $this->dependModel->getLabel($attr);
            $t .= '</div>';

            $t .= '<div class="row-value">';
            if ($this->tplBuilder->geViewType() != self::TYPE_VIEW_EDIT) {
                switch ($data['type']) {
                    case TemplateBuilder::VIEW_TYPE_SELECT:
                        $t .= $data['values'][$attr] ?? $this->dependModel->$attr;
                        break;
                    default:
                        $t .= $this->dependModel->$attr;
                }
            } else {
                $limit = $data['limit'] ?? null;
                switch ($data['type']) {
                    case TemplateBuilder::VIEW_TYPE_TEXT:
                        $t .= tHTML::inputText($attr, $this->dependModel->$attr, $limit);
                        break;
                    case TemplateBuilder::VIEW_TYPE_SELECT:
                        $values = $data['values'] ?? [];
                        $t .= tHTML::inputSelect($attr, $values, $this->dependModel->$attr);
                        break;
                }
            }
            $t .= '</div>';
        }

        $t .= '</div>';

        return $t;
    }


}
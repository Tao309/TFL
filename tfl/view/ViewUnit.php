<?php

namespace tfl\view;

use tfl\builders\TemplateBuilder;
use tfl\utils\tHTML;

class ViewUnit extends View
{
    protected function viewBody(): string
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

    protected function viewRow(string $attr, array $data): string
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

                $inputName = $this->dependModel->getModelNameLower() . '[' . $attr . ']';

                switch ($data['type']) {
                    case TemplateBuilder::VIEW_TYPE_TEXT:
                        $t .= tHTML::inputText($inputName, $this->dependModel->$attr, $limit);
                        break;
                    case TemplateBuilder::VIEW_TYPE_SELECT:
                        $values = $data['values'] ?? [];
                        $t .= tHTML::inputSelect($inputName, $values, $this->dependModel->$attr);
                        break;
                }
            }
            $t .= '</div>';
        }

        $t .= '</div>';

        return $t;
    }

}
<?php

namespace tfl\view;

use tfl\builders\DbBuilder;
use tfl\builders\RequestBuilder;
use tfl\builders\TemplateBuilder;
use tfl\units\UnitOption;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;

class ViewUnit extends View
{
    protected function viewBody(): string
    {
        $t = '<div class="section-option-list type-' . $this->tplBuilder->geViewType() . '">';
        $t .= '<div class="view-model-table view-edit">';

        $elements = '';
        foreach ($this->tplBuilder->viewData() as $attr => $data) {
            $elements .= $this->viewRow($attr, $data);
        }

        if ($this->tplBuilder->geViewType() == self::TYPE_VIEW_EDIT) {
            $elements .= $this->viewActionRow();
            $data = ['admin/section', 'option', lcfirst($this->dependModel->getModelName())];

            $t .= tHtmlForm::simpleForm($data, $elements, [], RequestBuilder::METHOD_PUT);
        } else {
            $t .= $elements;
        }

        $t .= '</div>';
        $t .= '</div>';
        return $t;
    }

    protected function viewRow(string $attr, array $data): string
    {
        $class = 'type-' . $data['type'];
        if ($data['type'] == TemplateBuilder::VIEW_TYPE_HEADER) {
            $class = 'view-row-' . $data['type'];
        }

        $t = '<div class="view-row ' . $class . '">';

        if ($data['type'] == TemplateBuilder::VIEW_TYPE_HEADER) {
            $t .= '<div class="view-td-title">';
            $t .= $data['title'];
            $t .= '</div>';
        } else {
            if ($this->dependModel instanceof UnitOption) {
                /**
                 * @var $this ->dependModel UnitOption
                 */
                $defaultValue = $this->dependModel->getOptionValue($attr);
            } else {
                $defaultValue = $this->dependModel->$attr;
            }

            $t .= '<div class="view-td-title">';
            $t .= $this->dependModel->getLabel($attr);
            $t .= '</div>';

            $t .= '<div class="view-td-value">';

            if ($this->tplBuilder->geViewType() == self::TYPE_VIEW_EDIT) {
                $limit = $data['limit'] ?? null;

                $inputName = $this->dependModel->getModelName() . '[' . $attr . ']';

                $options = [];
                if (isset($data['disabled'])) $options['disabled'] = true;
                if (isset($data['readonly'])) {
                    $defaultValue = $data['value'] ?? $defaultValue;
                    $options['readonly'] = true;
                }

                switch ($data['type']) {
                    case TemplateBuilder::VIEW_TYPE_CHECKBOX:
                        $t .= tHTML::inputCheckbox($inputName, $defaultValue);
                        break;
                    case TemplateBuilder::VIEW_TYPE_TEXT:
                        $t .= tHTML::inputText($inputName, $defaultValue, $limit, $options);
                        break;
                    case TemplateBuilder::VIEW_TYPE_SELECT:
                        $values = $data['values'] ?? [];
                        $t .= tHTML::inputSelect($inputName, $values, $defaultValue);
                        break;
                }
            } else {
                switch ($data['type']) {
                    case TemplateBuilder::VIEW_TYPE_SELECT:
                        $t .= $data['values'][$attr] ?? $defaultValue;
                        break;
                    default:
                        $t .= $defaultValue;
                }
            }

            $t .= '</div>';
        }

        $t .= '</div>';

        return $t;
    }

    private function viewActionRow()
    {
        $t = '';

        $viewType = $this->tplBuilder->geViewType();

        if (in_array($viewType, [View::TYPE_VIEW_EDIT, View::TYPE_VIEW_ADD])) {
            $buttonTitle = 'Save';
            $showButton = true;
            if ($viewType == View::TYPE_VIEW_ADD) $buttonTitle = 'Add';

            $t .= '<div class="view-row view-row-action">';
            $t .= '<div class="view-td-action">';

            if ($showButton) {
                $t .= '<button class="html-element html-button" type="submit">' . $buttonTitle . '</button>';
            }

//            if($viewType == ViewList::TYPE_VIEW_EDIT)
//            {
//                $t .= '<button class="html-element html-button html-button-delete http-request-button" type="button" ';
//                $t .= ' '.tHtmlForm::generateElementData([
//                        "admin/section",
//                        $this->dependModel->getModelNameLower(),
//                        DbBuilder::TYPE_DELETE
//                    ], 'POST');
//
////                $t .= ' '.tHTML::generateDataParams(['id' => $this->dependModel->id]);
//                $t .= '>Delete</button>';
//            }

            $t .= '</div>';
            $t .= '</div>';
        }

        return $t;
    }

}
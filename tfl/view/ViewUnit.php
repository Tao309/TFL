<?php

namespace tfl\view;

use tfl\builders\RequestBuilder;
use tfl\builders\TemplateBuilder;
use tfl\units\UnitOption;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;

class ViewUnit extends View
{
    protected function viewBody(): string
    {
        $t = tHtmlTags::startTag('div', [
            'class' => [
                'section-option-list',
                'type-' . $this->tplBuilder->geViewType()
            ]
        ]);

        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'view-model-table',
                'view-edit',
            ]
        ]);

        $elements = '';
        foreach ($this->tplBuilder->viewData() as $attr => $data) {
            $elements .= $this->viewRow($attr, $data);
        }

        if ($this->tplBuilder->geViewType() == static::TYPE_VIEW_EDIT) {
            $elements .= $this->viewActionRow();

            if ($this->dependModel instanceof UnitOption) {
                $data = [
                    $this->tplBuilder->getRouteDirectionLink(),
                    'option',
                    lcfirst($this->dependModel->getModelName())
                ];
            } else {
                $data = [
                    $this->tplBuilder->getRouteDirectionLink(),
                    $this->dependModel->getModelNameLower() . '/' . $this->dependModel->id,
                    static::TYPE_VIEW_SAVE
                ];
            }

            $t .= tHtmlForm::simpleForm($data, $elements, [], RequestBuilder::METHOD_PUT);
        } else {
            $t .= $elements;
        }

        $t .= tHtmlTags::endTag('div');
        $t .= tHtmlTags::endTag('div');

        return $t;
    }

    protected function viewRow(string $attr, array $data): string
    {
        $class = 'type-' . $data['type'];
        if ($data['type'] == TemplateBuilder::VIEW_TYPE_HEADER) {
            $class = 'view-row-' . $data['type'];
        }

        $t = tHtmlTags::startTag('div', [
            'class' => [
                'view-row',
                $class,
            ]
        ]);

        if ($data['type'] == TemplateBuilder::VIEW_TYPE_HEADER) {
            $t .= tHtmlTags::render('div', $data['title'], [
                'class' => 'view-td-title'
            ]);
        } else {
            if ($this->dependModel instanceof UnitOption) {
                /**
                 * @var $this ->dependModel UnitOption
                 */
                $defaultValue = $this->dependModel->getOptionValue($attr);
            } else {
                $defaultValue = $this->dependModel->$attr;
            }

            $t .= tHtmlTags::render('div', $this->dependModel->getLabel($attr), [
                'class' => 'view-td-title'
            ]);

            $t .= tHtmlTags::startTag('div', [
                'class' => 'view-td-value'
            ]);


            if ($this->tplBuilder->geViewType() == self::TYPE_VIEW_EDIT) {
                //EditView
                $limit = $data['limit'] ?? null;

                $inputName = $this->dependModel->getModelName() . '[' . $attr . ']';

                $options = [];
                if (isset($data['disabled'])) $options['disabled'] = true;
                if (isset($data['readonly'])) {
                    $defaultValue = $data['value'] ?? $defaultValue;
                    $options['readonly'] = true;
                }

                switch ($data['type']) {
                    case TemplateBuilder::VIEW_TYPE_TEXTAREA:
                        $t .= tHTML::inputTextarea($inputName, $defaultValue, $limit, $options);
                        break;
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
                    case TemplateBuilder::VIEW_TYPE_MODEL:
                        $t .= $this->viewRelationModelRow($attr);
                        break;
                }
            } else {
                //DetailsView
                switch ($data['type']) {
                    case TemplateBuilder::VIEW_TYPE_SELECT:
                        $t .= $data['values'][$defaultValue] ?? null;
                        break;
                    case TemplateBuilder::VIEW_TYPE_MODEL:
                        $t .= $this->viewRelationModelRow($attr);
                        break;
                    default:
                        $t .= $defaultValue;
                }
            }

            $t .= tHtmlTags::endTag('div');
        }

        $t .= tHtmlTags::endTag('div');

        return $t;
    }

    /**
     * Отображение кнопок действий
     * @return string
     */
    private function viewActionRow(): string
    {
        $t = '';

        $viewType = $this->tplBuilder->geViewType();

        if (in_array($viewType, [View::TYPE_VIEW_EDIT, View::TYPE_VIEW_ADD])) {
            $buttonTitle = 'Save';
            $showButton = true;
            if ($viewType == View::TYPE_VIEW_ADD) $buttonTitle = 'Add';

            $t .= tHtmlTags::startTag('div', [
                'class' => [
                    'view-row',
                    'view-row-action',
                ]
            ]);

            $t .= tHtmlTags::startTag('div', [
                'class' => 'view-td-action'
            ]);

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

            $t .= tHtmlTags::endTag('div');
            $t .= tHtmlTags::endTag('div');
        }

        return $t;
    }

    /**
     * Отображение строки по relations модели
     * @param string $attr
     * @return string
     */
    private function viewRelationModelRow(string $attr)
    {
        return $this->getViewHandler($attr)->renderRowField();
    }

}
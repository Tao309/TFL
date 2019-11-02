<?php

namespace tfl\view;

use tfl\builders\DbBuilder;
use tfl\builders\RequestBuilder;
use tfl\builders\TemplateBuilder;
use tfl\handlers\html\BbTags;
use tfl\units\UnitOption;
use tfl\utils\tAccess;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;
use tfl\utils\tString;

class ViewUnit extends View
{
    public function __construct(TemplateBuilder $tplBuilder)
    {
        parent::__construct($tplBuilder);

        $this->initViewHandlers();
    }

    protected function prepareViewLoad()
    {
        return;
    }

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

        if (in_array($this->tplBuilder->geViewType(), [static::TYPE_VIEW_ADD, static::TYPE_VIEW_EDIT])) {
            $elements .= $this->viewActionRow();

            $method = RequestBuilder::METHOD_PUT;
            if ($this->dependModel instanceof UnitOption) {
                $data = [
                    $this->tplBuilder->getRouteDirectionLink(),
                    'option',
                    lcfirst($this->dependModel->getModelName())
                ];
            } else {
                if ($this->tplBuilder->geViewType() == static::TYPE_VIEW_ADD) {
                    $data = [
                        $this->tplBuilder->getRouteDirectionLink(),
                        $this->dependModel->getModelNameLower(),
                        static::TYPE_VIEW_CREATE
                    ];
                    $method = RequestBuilder::METHOD_POST;
                } else {
                    $data = [
                        $this->tplBuilder->getRouteDirectionLink(),
                        $this->dependModel->getModelNameLower() . '/' . $this->dependModel->id,
                        static::TYPE_VIEW_SAVE
                    ];
                }
            }

            $t .= tHtmlForm::simpleForm($data, $elements, [], $method);
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


            if (in_array($this->tplBuilder->geViewType(), [self::TYPE_VIEW_ADD, self::TYPE_VIEW_EDIT])) {
                //AddView
                //EditView
                $limit = $data['limit'] ?? null;

                $inputName = $this->dependModel->getModelName() . '[' . $attr . ']';

                $options = [];
                if (isset($data['disabled'])) {
                    $options['disabled'] = true;
                }
                if (isset($data['readonly'])) {
                    $defaultValue = $data['value'] ?? $defaultValue;
                    $options['readonly'] = true;
                }
                if (isset($data['required']) && $data['required']) {
                    $options['class'] = ['element-required'];
                }

                switch ($data['type']) {
                    case TemplateBuilder::VIEW_TYPE_TEXTAREA:
                        if (isset($data['bbTags']) && $data['bbTags']) {
                            //Добавляем строку с бб-тэгами
                            $bbTags = new BbTags($this->dependModel, $attr);
                            $t .= $bbTags->render();
                        }

                        $t .= tHTML::inputTextarea($inputName, tString::fromDbToTextarea($defaultValue),
                            $limit, $options);
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
                    case TemplateBuilder::VIEW_TYPE_TEXTAREA:
                        $t .= BbTags::replaceTags($defaultValue);
                        break;
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

            $t .= tHtmlTags::startTag('div', [
                'class' => [
                    'view-row',
                    'view-row-action',
                ]
            ]);

            $t .= tHtmlTags::startTag('div', [
                'class' => 'view-td-action'
            ]);

            if ($viewType == View::TYPE_VIEW_ADD) {
                //AddView
                if (tAccess::canAdd($this->dependModel)) {
                    $t .= tHTML::inputSubmitButton('submit', 'Add');
                }
            } else {
                //EditView
                if (tAccess::canEdit($this->dependModel)) {
                    $t .= tHTML::inputSubmitButton('submit', 'Save');
                }

                if (tAccess::canDelete($this->dependModel)) {
                    $htmlData = tHtmlForm::generateElementData([
                        $this->tplBuilder->getRouteDirectionLink(),
                        $this->dependModel->getModelName() . '/' . $this->dependModel->id,
                        DbBuilder::TYPE_DELETE,
                    ], RequestBuilder::METHOD_POST);

                    $t .= tHTML::inputActionButton('Delete', 'Delete', $htmlData, [
                        'class' => [
                            'html-element',
                            'html-button',
                            'html-button-delete',
                        ],
                        'title' => 'Delete',
                        'data-params' => tHtmlForm::generateDataParams($this->dependModel->getHiddenActionData(DbBuilder::TYPE_DELETE), true),
                    ]);
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
            }


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
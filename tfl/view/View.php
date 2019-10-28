<?php

namespace tfl\view;

use tfl\builders\RequestBuilder;
use tfl\builders\TemplateBuilder;
use tfl\units\Unit;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;

class View
{
    const TYPE_VIEW_DETAILS = 'details';
    const TYPE_VIEW_EDIT = 'edit';
    const TYPE_VIEW_DELETE = 'delete';
    const TYPE_VIEW_ADD = 'add';
    const TYPE_VIEW_LIST = 'list';

    /**
     * @var $tplBuilder TemplateBuilder
     */
    protected $tplBuilder;
    /**
     * @var $dependModel Unit
     */
    protected $dependModel;

    public function __construct(TemplateBuilder $tplBuilder)
    {
        $this->tplBuilder = $tplBuilder;
        $this->dependModel = $tplBuilder->getDependModel();
    }

    public function render(): string
    {
        $t = $this->viewHeader();
        $t .= $this->viewBody();
        $t .= $this->viewFooter();

        return $t;
    }

    private function viewHeader(): string
    {
        $t = tHtmlTags::startTag('div', [
            'class' => 'section-header'
        ]);
        $t .= tHtmlTags::render('div', $this->tplBuilder->viewTitle(), [
            'class' => 'header'
        ]);
        $t .= tHtmlTags::endTag('div');
        return $t;
    }

    private function viewFooter(): string
    {
        //@todo Вывод кнопок сохранения
        return '';
    }


}
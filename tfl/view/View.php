<?php

namespace tfl\view;

use tfl\builders\TemplateBuilder;
use tfl\units\Unit;
use tfl\utils\tHTML;

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

    private function viewFooter(): string
    {
        return '';
    }



}
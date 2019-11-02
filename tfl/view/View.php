<?php

namespace tfl\view;

use app\models\Image;
use tfl\builders\TemplateBuilder;
use tfl\handlers\view\ImageViewHandler;
use tfl\interfaces\view\ViewHandlerInterface;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\utils\tAccess;
use tfl\utils\tHTML;
use tfl\utils\tHtmlTags;

/**
 * Class View
 * @package tfl\view
 *
 * @property TemplateBuilder $tplBuilder
 * @property UnitActive $dependModel
 * @property ViewHandlerInterface[] $viewHandlers
 */
abstract class View
{
    const TYPE_VIEW_DETAILS = 'details';
    const TYPE_VIEW_EDIT = 'edit';
    const TYPE_VIEW_SAVE = 'save';//Только для route section ajax
    const TYPE_VIEW_DELETE = 'delete';
    const TYPE_VIEW_ADD = 'add';
    const TYPE_VIEW_LIST = 'list';
    const TYPE_VIEW_CREATE = 'create';

    /**
     * @var $tplBuilder TemplateBuilder
     */
    protected $tplBuilder;
    /**
     * @var $dependModel Unit
     */
    protected $dependModel;

    /**
     * @var array ViewHandlerInterface[]
     */
    protected $viewHandlers = [];

    public function __construct(TemplateBuilder $tplBuilder)
    {
        $this->tplBuilder = $tplBuilder;
        $this->dependModel = $tplBuilder->getDependModel();

        $this->prepareViewLoad();
    }

    abstract protected function prepareViewLoad();

    protected function initViewHandlers()
    {
        foreach ($this->dependModel->unitData()['relations'] as $attr => $data) {
            if ($data['model'] == Image::class) {
                $this->viewHandlers[$attr] = new ImageViewHandler($this->dependModel, $attr,
                    $this->tplBuilder->geViewType());
            }
        }
    }

    protected function getViewHandler($attr)
    {
        return $this->viewHandlers[$attr];
    }

    public function render(): string
    {
        $t = $this->viewHeader();

        if ($this->tplBuilder->geViewType() == View::TYPE_VIEW_LIST) {
            $t .= $this->renderHeaderButtons();
        }

        $t .= tHtmlTags::startTag('div', [
            'class' => 'section-' . $this->tplBuilder->geViewType(),
        ]);
        $t .= $this->viewBody();
        $t .= tHtmlTags::endTag('div');

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
        $t .= tHtmlTags::endTag();
        return $t;
    }

    private function viewFooter(): string
    {
        $t = '';
        return $t;
    }

    private function renderHeaderButtons()
    {
        $t = tHtmlTags::startTag('div', [
            'class' => 'section-option-buttons'
        ]);

        if (tAccess::canAdd($this->dependModel)) {
            $t .= tHTML::inputLink($this->dependModel->getAddUrl(), 'Add', [
                'class' => [
                    'html-button',
                    'html-button-add',
                ],
                'title' => 'Add',
            ]);
        }


        $t .= tHtmlTags::endTag();
        return $t;
    }


}
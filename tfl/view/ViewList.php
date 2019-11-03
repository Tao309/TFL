<?php

namespace tfl\view;

use app\models\Image;
use tfl\builders\DbBuilder;
use tfl\builders\RequestBuilder;
use tfl\collections\UnitActiveCollection;
use tfl\units\Unit;
use tfl\units\UnitActive;
use tfl\utils\tAccess;
use tfl\utils\tHTML;
use tfl\utils\tHtmlForm;
use tfl\utils\tHtmlTags;
use tfl\utils\tString;

/**
 * Class ViewList
 * @package tfl\view
 *
 * @property array $columns
 * @property string $link
 * @property int $offset
 * @property int $perPage
 * @property int $currentPage
 */
class ViewList extends View
{
    private $columns;
    private $link;
    private $offset;
    private $perPage;
    private $currentPage;

    protected function prepareViewLoad(): void
    {
        $columns = $this->tplBuilder->viewData()['columns'];
        $this->columns = array_map('strtolower', $columns);

        $this->perPage = $this->tplBuilder->viewData()['perPage'] ?? 30;

        $currentPage = \TFL::source()->request->getRequestValue('get', 'page');
        $this->currentPage = is_numeric($currentPage) ? tString::encodeNum($currentPage) : 1;

        if ($this->currentPage > 1) {
            $this->offset = (($this->currentPage - 1) * $this->perPage);
        } else {
            $this->offset = 0;
        }

        $this->link = ROOT . 'admin/section/' . $this->dependModel->getModelNameLower();
    }

    private function viewHeaderRow(string $attr, $title = null): string
    {
        return tHtmlTags::render('div', $title ?? $this->dependModel->getLabel($attr), [
            'class' => [
                'view-td-header',
                'attr-' . $attr,
            ]
        ]);
    }

    protected function viewBody(): string
    {
        $t = tHtmlTags::startTag('div', [
            'class' => [
                'view-list-table',
            ]
        ]);

        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'view-thead',
            ]
        ]);
        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'view-row-header',
            ]
        ]);
        $t .= $this->viewHeaderRow('id', 'ID');
        foreach ($this->columns as $attr) {
            $t .= $this->viewHeaderRow($attr);
        }
        $t .= $this->viewHeaderRow('createddatetime', 'Created Date');
        $t .= $this->viewHeaderRow('lastchangedatetime', 'Change Date');
        $t .= $this->viewHeaderRow('owner', 'Owner');
        $t .= $this->viewHeaderRow('actions', 'Action');

        $t .= tHtmlTags::endTag('div');
        $t .= tHtmlTags::endTag('div');

        $collection = new UnitActiveCollection($this->dependModel);
        $collection->setOffset($this->offset);
        $collection->setPerPage($this->perPage);
        $collection->withOwner();

        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'view-tbody',
            ]
        ]);

        $t .= $this->viewRows($collection->getModels());

        $t .= tHtmlTags::endTag();

        $t .= tHtmlTags::endTag();

        $t .= $this->renderPages($collection);

        return $t;
    }

    /**
     * @param $rows array|Unit[]
     * @return string
     */
    private function viewRows(\Iterator $models): string
    {
        $t = '';

        foreach ($models as $model) {
            $t .= tHtmlTags::startTag('div', [
                'class' => [
                    'view-row',
                ],
                'id' => $model->getHtmlElementId(),
            ]);

            $t .= $this->viewColumn('id', $model->id);

            foreach ($this->columns as $index => $attr) {
                $value = $model->$attr ?? '---';

                if ($index == 0) {
                    $value = tHTML::inputLink($model->getEditUrl(), $value);
                }

                $t .= $this->viewColumn($attr, $value);
            }

            $t .= $this->viewColumn('createddatetime', tString::getDatetime($model->createdDateTime, 'd.m.Y H:i'));
            $t .= $this->viewColumn('lastchangedatetime', tString::getDatetime($model->lastChangeDateTime, 'd.m.Y H:i'));

            //Добавлять даныне о владельце и изменении
            $t .= $this->viewOwnerColumn($model);

            //Добавить действия
            $t .= $this->viewActionColumn($model);

            $t .= tHtmlTags::endTag('div');
        }

        return $t;
    }

    private function viewColumn($attr, $value)
    {
        $t = tHtmlTags::startTag('div', [
            'class' => [
                'view-td',
                'attr-' . $attr,
            ]
        ]);

        $t .= $value;
        $t .= tHtmlTags::endTag('div');

        return $t;
    }

    private function viewOwnerColumn(UnitActive $model)
    {
        $t = tHTML::inputLink($model->owner->getUrl(), $model->owner);
        return $this->viewColumn('owner', $t);
    }

    private function viewActionColumn(UnitActive $model)
    {
        $t = '';
        if (tAccess::canView($model)) {
            $t .= tHTML::inputLink($model->getUrl(), '', [
                'class' => [
                    'html-icon-button',
                    'icon-open',
                    'font-icon-tfl',
                ],
                'title' => 'Open',
            ]);
        }
        if (tAccess::canEdit($model)) {
            $t .= tHTML::inputLink($model->getEditUrl(), '', [
                'class' => [
                    'html-icon-button',
                    'icon-edit',
                    'font-icon-tfl',
                ],
                'title' => 'Edit',
            ]);
        }
        if (tAccess::canDelete($model)) {
            $htmlData = tHtmlForm::generateElementData([
                $this->tplBuilder->getRouteDirectionLink(),
                $model->getModelName() . '/' . $model->id,
                DbBuilder::TYPE_DELETE,
            ], RequestBuilder::METHOD_POST);

            $t .= tHTML::inputActionButton('Delete', '', $htmlData, [
                'class' => [
                    'html-icon-button',
                    'icon-delete',
                    'font-icon-tfl',
                ],
                'title' => 'Delete',
                'data-params' => tHtmlForm::generateDataParams($model->getHiddenActionData(DbBuilder::TYPE_DELETE), true),
            ]);
        }

        return $this->viewColumn('actions', $t);

    }

    private function renderPages(UnitActiveCollection $collection): string
    {
        $count = $collection->getAllCount();

        $countPages = ceil($count / $this->perPage);
        if ($countPages <= 1) {
            return '';
        }

        $t = tHtmlTags::startTag('div', [
            'class' => 'section-pages',
        ]);

        for ($i = 1; $i <= $countPages; $i++) {
            $options = [
                'class' => ['one-page'],
            ];
            if ($i == $this->currentPage) {
                $options['class'][] = 'current-page';
            }

            $opt = [];
            if ($i > 1) {
                $opt = ['page' => $i];
            }
            $currentLink = tHTML::addParamsToLink($this->link, $opt);

            $t .= tHTML::inputLink($currentLink, $i, $options);
        }

        $t .= tHtmlTags::endTag('div');

        return $t;
    }

}
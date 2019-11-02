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
 * @property int $perPage
 */
class ViewList extends View
{
    private $columns;
    private $perPage;

    protected function prepareView()
    {
        $columns = $this->tplBuilder->viewData()['columns'];
        $this->columns = array_map('strtolower', $columns);

        $this->perPage = $this->tplBuilder->viewData()['perPage'] ?? 30;
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
        $collection->setPerPage($this->perPage);
        $collection->withOwner();

        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'view-tbody',
            ]
        ]);
        $t .= $this->viewRows($collection->getModels());
        $t .= tHtmlTags::endTag('div');

        $t .= tHtmlTags::endTag('div');

        return $t;
    }

    /**
     * @param $rows array|Unit[]
     * @return string
     */
    private function viewRows($rows): string
    {
        $t = '';

        $isModel = (isset($rows[0]) && $rows[0] instanceof UnitActive);

        foreach ($rows as $row) {
            $t .= tHtmlTags::startTag('div', [
                'class' => [
                    'view-row',
                ],
                'id' => $isModel ? $row->getHtmlElementId() : '',
            ]);

            if ($isModel) {
                $t .= $this->viewColumn('id', $row->id);
            }

            foreach ($this->columns as $index => $attr) {
                if ($isModel) {
                    $value = $row->$attr ?? '---';

                    if ($index == 0) {
                        $value = tHTML::inputLink($row->getEditUrl(), $value);
                    }

                } else {
                    $value = $row[$attr] ?? '---';
                }

                $t .= $this->viewColumn($attr, $value);
            }

            if ($isModel) {
                $t .= $this->viewColumn('createddatetime', tString::getDatetime($row->createdDateTime, 'd.m.Y H:i'));
                $t .= $this->viewColumn('lastchangedatetime', tString::getDatetime($row->lastChangeDateTime, 'd.m.Y H:i'));

                //Добавлять даныне о владельце и изменении
                $t .= $this->viewOwnerColumn($row);

                //Добавить действия
                $t .= $this->viewActionColumn($row);
            }

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
                'section',
                'page/' . $model->id,
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

}
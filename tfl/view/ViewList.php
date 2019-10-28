<?php

namespace tfl\view;

use tfl\builders\TemplateBuilder;
use tfl\collections\UnitActiveCollection;
use tfl\units\Unit;
use tfl\utils\tHtmlTags;

class ViewList extends View
{
    protected function viewBody(): string
    {
        $t = tHtmlTags::startTag('div', [
            'class' => [
                'view-body',
                'type-' . $this->tplBuilder->geViewType(),
            ]
        ]);
        $t .= tHtmlTags::startTag('div', [
            'class' => [
                'view-row',
                'type-row-header',
            ]
        ]);
        foreach ($this->tplBuilder->viewData() as $attr => $data) {
            $t .= $this->viewHeaderRow($attr, $data);
        }
        $t .= tHtmlTags::endTag('div');

        $collection = new UnitActiveCollection($this->dependModel);
        $collection->setAttributes(array_keys($this->tplBuilder->viewData()));
        $rows = $collection->getModels();

        $t .= $this->viewRows($rows);

        $t .= tHtmlTags::endTag('div');
        return $t;
    }

    private function viewHeaderRow(string $attr, array $data): string
    {
        return tHtmlTags::render('div', $this->dependModel->getLabel($attr), [
            'class' => [
                'column',
                'name-' . $attr,
            ]
        ]);
    }

    /**
     * @param $rows array|Unit[]
     * @return string
     */
    private function viewRows($rows): string
    {
        $t = '';

        $isModel = (isset($rows[0]) && $rows[0] instanceof Unit);

        foreach ($rows as $row) {
            $t .= tHtmlTags::startTag('div', [
                'class' => [
                    'view-row',
                    'type-row',
                ]
            ]);
            foreach ($this->tplBuilder->viewData() as $attr => $data) {
                $t .= tHtmlTags::startTag('div', [
                    'class' => [
                        'column',
                        'name-' . $attr,
                    ]
                ]);

                if ($isModel) {
                    $t .= $row->$attr;
                } else {
                    $t .= $row[$attr] ?? null;
                }

                $t .= tHtmlTags::endTag('div');
            }
            $t .= tHtmlTags::endTag('div');
        }

        return $t;
    }

}
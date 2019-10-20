<?php

namespace tfl\view;

use tfl\builders\TemplateBuilder;
use tfl\collections\UnitActiveCollection;

class ViewList extends View
{
    protected function viewBody(): string
    {
        $t = '<div class="view-body type-' . $this->tplBuilder->geViewType() . '">';

        $t .= '<div class="view-row type-header">';
        foreach ($this->tplBuilder->viewData() as $attr => $data) {
            $t .= $this->viewHeaderRow($attr, $data);
        }
        $t .= '</div>';


        $collection = new UnitActiveCollection($this->dependModel);
        $showAttrs = array_keys($this->tplBuilder->viewData());
        $collection->setAttributes($showAttrs);

        $rows = $collection->getAll();

        foreach ($rows as $row) {
            $t .= '<div class="view-row type-row">';
            foreach ($this->tplBuilder->viewData() as $attr => $data) {
                $t .= '<div class="column name-' . $attr . '">';
                $t .= $row[$attr];
                $t .= '</div>';
            }
            $t .= '</div>';
        }


        $t .= '</div>';
        return $t;
    }

    private function viewHeaderRow(string $attr, array $data): string
    {
        $t = '<div class="column name-' . $attr . '">';
        $t .= $this->dependModel->getLabel($attr);
        $t .= '</div>';

        return $t;
    }
}
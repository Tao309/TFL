<?php

namespace tfl\view;

use tfl\builders\TemplateBuilder;
use tfl\collections\UnitActiveCollection;
use tfl\units\Unit;

class ViewList extends View
{
    protected function viewBody(): string
    {
        $t = '<div class="view-body type-' . $this->tplBuilder->geViewType() . '">';

        $t .= '<div class="view-row type-row-header">';
        foreach ($this->tplBuilder->viewData() as $attr => $data) {
            $t .= $this->viewHeaderRow($attr, $data);
        }
        $t .= '</div>';

        $collection = new UnitActiveCollection($this->dependModel);
        $collection->setAttributes(array_keys($this->tplBuilder->viewData()));
        $rows = $collection->getModels();

        $t .= $this->viewRows($rows);

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

    /**
     * @param $rows array|Unit[]
     * @return string
     */
    private function viewRows($rows): string
    {
        $t = '';

        $isModel = (isset($rows[0]) && $rows[0] instanceof Unit);

        foreach ($rows as $row) {
            $t .= '<div class="view-row type-row">';
            foreach ($this->tplBuilder->viewData() as $attr => $data) {
                $t .= '<div class="column name-' . $attr . '">';

                if ($isModel) {
                    $t .= $row->$attr;
                } else {
                    $t .= $row[$attr] ?? null;
                }

                $t .= '</div>';
            }
            $t .= '</div>';
        }

        return $t;
    }

}
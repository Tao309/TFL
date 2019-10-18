<?php

namespace tfl\observers;

trait DB
{
    private $_select;
    private $_from;
    private $_leftJoin;
    private $_where;

    private $init = false;

    private function setInit()
    {
        $this->init = true;
    }

    private function restoreInit()
    {
        $this->init = false;
    }

    public function getInit()
    {
        return $this->init;
    }

    //Учитывается сортировка по мере прохода
    private static $sort = [
        'select',
        'from',
        'leftJoin',
        'where',
    ];

    public function select($input = null)
    {
        $this->setInit();

        $input = $input ?? '*';

        $this->_select = 'SELECT ' . $input;

        return $this;
    }

    public function from($input)
    {
        $this->_from = 'FROM ' . $input;
        return $this;
    }

    public function leftJoin($input, $cond)
    {
        $this->_leftJoin = 'LEFT JOIN ' . $input . ' ON (' . $cond . ')';
        return $this;
    }

    public function where($input, $args = [])
    {
        if ($args) {
            foreach ($args as $index => $arg) {
                //@todo добавить check_sql
                if (is_string($arg)) {
                    $arg = '"' . $arg . '"';
                }
                $input = str_ireplace(':' . $index, $arg, $input);
            }
        }

        $this->_where = 'WHERE ' . $input;

        return $this;
    }

    //@todo Добавить защиту
    public function getSqlRow()
    {
        return $this->getText();
    }

    public function getText()
    {
        $return = '';
        foreach (self::$sort as $index => $value) {
            $attr_name = '_' . $value;

            if (!empty($this->$attr_name)) {
                $return .= $this->$attr_name . PAGE_EOL;
            }
        }

        $this->restoreInit();

        return $return;
    }
}
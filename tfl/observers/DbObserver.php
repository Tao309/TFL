<?php

namespace tfl\observers;

trait DbObserver
{
    // @todo Сделать адекватное построение
    private $_select;
    private $_from;
    private $_leftJoin;
    private $_where;
    private $_limit;

    private $init = false;

    private function setInit()
    {
        $this->init = true;
    }

    private function restoreInit()
    {
        $this->init = false;

        foreach (self::$sort as $index => $value) {
            $attr = '_' . $value;
            $this->$attr = null;
        }

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
        'limit',
    ];

    public function select($input = null)
    {
        $this->setInit();

        $input = $input ?? '*';

        $this->_select = 'SELECT ' . $input;

        return $this;
    }

    public function addSelect($input)
    {
        if (empty($this->_select)) {
            die('Select fields required for query');
        }

        $this->_select .= ',' . PAGE_EOL;
        $this->_select .= $input;

        return $this;
    }

    public function from($input)
    {
        $this->_from = 'FROM ' . $input;
        return $this;
    }

    public function leftJoin($input, $cond)
    {
        $this->_leftJoin .= 'LEFT JOIN ' . $input . ' ON (' . $cond . ')';
        $this->_leftJoin .= PAGE_EOL;
        return $this;
    }

    public function where($input, $args = [], $add = false)
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

        if ($add && !empty($this->_where)) {
            $this->_where .= ' AND (' . $input . ')';
        } else {
            $this->_where = 'WHERE (' . $input . ')';
        }

        return $this;
    }

    public function andWhere($input, $args = [])
    {
        return $this->where($input, $args, true);
    }

    public function limit(int $offset, int $limit)
    {
        $this->_limit = 'LIMIT ' . $offset . ',' . $limit;

        return $this;
    }

    public function prepareValues(&$fullValueName, &$value, &$valueName = null)
    {
        $options = explode('|', $fullValueName);
        if (count($options) > 1) {
            $fullValueName = $options[0];
            array_shift($options);

            foreach ($options as $option) {
                switch ($option) {
                    case 'lower':
                        $fullValueName = 'LOWER(' . $fullValueName . ')';
                        $value = mb_strtolower($value);
                        break;
                }
            }
        }

        if ($valueName) {
            $options = explode('|', $valueName);
            if (count($options) > 1) {
                $valueName = $valueName[0];
            }

        }
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
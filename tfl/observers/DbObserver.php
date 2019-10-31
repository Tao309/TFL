<?php

namespace tfl\observers;

use tfl\utils\tString;

trait DbObserver
{
    // @todo Сделать адекватное построение
    private $_select;
    private $_from;
    private $_leftJoin;
    private $_rightJoin;
    private $_innerJoin;
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
        'rightJoin',
        'innerJoin',
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

    public function innerJoin($input, $cond)
    {
        $this->_innerJoin .= 'INNER JOIN ' . $input . ' ON (' . $cond . ')';
        $this->_innerJoin .= PAGE_EOL;
        return $this;
    }

    public function leftJoin($input, array $cond)
    {
        $this->_leftJoin .= 'LEFT JOIN ' . $input . ' ON (' . PAGE_EOL;

        $where = [];
        foreach ($cond as $name => $value) {
            $where[] = $name . ' = ' . $value;
        }

        $this->_leftJoin .= implode(' AND ', $where) . PAGE_EOL;
        $this->_leftJoin .= ')' . PAGE_EOL;
        return $this;
    }

    public function rightJoin($input, $cond)
    {
        $this->_leftJoin .= 'RIGHT JOIN ' . $input . ' ON (' . $cond . ')';
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

    public function insert(string $table, array $sliceValues, array $duplicateUpdate = [])
    {
        list($names, $values) = $this->getSliceValues($sliceValues);

        $query = 'INSERT INTO ' . $table . PAGE_EOL;
        $query .= "(" . implode(',', $names) . ")" . PAGE_EOL;
        $query .= "VALUES(" . implode(',', $values) . ")" . PAGE_EOL;

        if (!empty($duplicateUpdate)) {
            $query .= 'ON DUPLICATE KEY UPDATE' . PAGE_EOL;
            $dupl = [];
            foreach ($duplicateUpdate as $index => $value) {
                $dupl[] = $table . '.' . $value . ' = VALUES(' . $table . '.' . $value . ')';
            }
            $query .= implode(', ', $dupl);
        }

        return $this->insertRow($query);
    }

    public function update(string $table, array $sliceValues, array $condition)
    {
        list($names, $values) = $this->getSliceValues($sliceValues);

        $query = 'UPDATE ' . $table . ' SET' . PAGE_EOL;
        $upd = [];
        foreach ($names as $index => $name) {
            $upd[] = $table . '.' . $name . ' = ' . $values[$index];
        }
        $query .= implode(', ', $upd) . PAGE_EOL;

        $where = [];
        foreach ($condition as $condName => $condValue) {
            $condValue = is_int($condValue) ? (int)$condValue : tString::checkString($condValue, true);
            $where[] = $table . '.' . $condName . ' = ' . $condValue;
        }
        $query .= 'WHERE ' . implode(' AND ', $where);

        return $this->updateRow($query);
    }

    public function delete(string $table, array $condition)
    {
        $query = 'DELETE FROM ' . $table . PAGE_EOL;

        $where = [];
        foreach ($condition as $condName => $condValue) {
            $condValue = is_int($condValue) ? (int)$condValue : '"' . tString::checkString($condValue, true) . '"';
            $where[] = $table . '.' . $condName . ' = ' . $condValue;
        }
        $query .= 'WHERE ' . implode(' AND ', $where);

        return $this->deleteRow($query);
    }

    private function getSliceValues(array $sliceValues)
    {
        $names = $values = [];
        foreach ($sliceValues as $index => $value) {
            $names[] = $index;
            $values[] = is_int($value) ? (int)$value : '"' . tString::checkString($value, true) . '"';
        }
        return [$names, $values];
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
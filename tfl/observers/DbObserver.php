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
    private $_group;
    private $_order;
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
        'group',
        'order',
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
            //@todo Улучшить
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

    private function join($type, $table, $cond)
    {
        $t = mb_strtoupper($type) . ' JOIN ' . $table . ' ON (' . PAGE_EOL;

        $where = [];
        foreach ($cond as $name => $value) {
            $where[] = $name . ' = ' . $value;
        }

        $t .= implode(' AND ', $where) . PAGE_EOL;
        $t .= ')' . PAGE_EOL;

        return $t;
    }

    public function innerJoin($input, $cond)
    {
        $this->_innerJoin .= $this->join('inner', $input, $cond);

        return $this;
    }
    public function leftJoin($input, array $cond)
    {
        $this->_leftJoin .= $this->join('left', $input, $cond);

        return $this;
    }

    public function rightJoin($input, $cond)
    {
        $this->_rightJoin .= $this->join('right', $input, $cond);

        return $this;
    }

    public function where($input, $args = [], $add = false, $or = false)
    {
        if ($args) {
            foreach ($args as $index => $arg) {
                $arg = tString::checkValue($arg);
                if (is_string($arg)) {
                    $arg = '"' . $arg . '"';
                }
                $input = str_ireplace(':' . $index, $arg, $input);
            }
        }

        if ($or && !empty($this->_where)) {
            $this->_where .= ' OR (' . $input . ')';
        } else if ($add && !empty($this->_where)) {
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

    public function orWhere($input, $args = [])
    {
        return $this->where($input, $args, false, true);
    }

    public function order($value, $type = 'ASC')
    {
        $this->_order = 'ORDER BY ' . $value;
        $this->_order .= ($type == 'ASC') ? ' ' . $type : ' DESC';

        return $this;
    }

    public function limit(int $offset, int $limit)
    {
        $this->_limit = 'LIMIT ' . $offset . ',' . $limit;

        return $this;
    }

    public function group($value)
    {
        if (empty($this->_group)) {
            $this->_group = 'GROUP BY ' . $value;
        } else {
            $this->_group .= ' AND  ' . $value;
        }

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
            if (is_array($condValue)) {
                if (empty($condValue)) {
                    continue;
                }

                $ids = [];
                foreach ($condValue as $id) {
                    $ids[] = tString::checkValue($id);
                }
                $where[] = $table . '.' . $condName . ' IN (' . implode(',', $ids) . ')';
            } else {
                $condValue = is_int($condValue) ? (int)$condValue : tString::checkString($condValue, true);
                $where[] = $table . '.' . $condName . ' = ' . $condValue;
            }
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

    public function getCount()
    {
        $this->select('COUNT(*) as count');
        return $this->find()['count'];
    }

    //@todo Добавить защиту
    protected function getSqlRow()
    {
        $text = $this->getText();

        $this->restoreInit();

        return $text;
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

        return $return;
    }
}
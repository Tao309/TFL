<?php

namespace tfl\observers;

use tfl\utils\tString;

trait DbObserver
{
	private $_select;
	private $_from;
	private $_leftJoin;
	private $_where;
	private $_group;
	private $_order;
	private $_limit;

	private $init = false;

	//Учитывается сортировка по мере прохода
	private static $sort = [
		'select',
		'from',
		'leftJoin',
		'where',
		'group',
		'order',
		'limit',
	];

	private static $availableConcatEquals = [
		self::CONCAT_EQUAL,
		self::CONCAT_NOT_EQUAL,
		self::CONCAT_IN,
		self::CONCAT_NOT_IN,
	];

	private function setInit(): void
	{
		$this->init = true;
	}

	private function restoreInit(): void
	{
		$this->init = false;

		foreach (self::$sort as $index => $value) {
			$this->{'_' . $value} = null;
		}
	}

	public function getInit(): bool
	{
		return $this->init;
	}

	public function select($input = null)
	{
		$this->setInit();

		$input = $input ?? '*';

		$this->_select = 'SELECT ' . $input;

		return $this;
	}

	public function addSelect(string $input)
	{
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

	public function leftJoin($input, array $cond)
	{
		$this->_leftJoin .= $this->join('left', $input, $cond);

		return $this;
	}

	private function getDefaultConcatEqual(string $value)
	{
		$value = mb_strtoupper($value);

		return in_array($value, self::$availableConcatEquals) ? $value : self::CONCAT_EQUAL;
	}

	private function compileWhereValue($input)
	{
		$nameColumn = $input[0];

		$concatEqual = self::CONCAT_EQUAL;

		$value = $input[1];
		if (isset($input[2])) {
			$concatEqual = $this->getDefaultConcatEqual($input[1]);
			$value = $input[2];
		}

		$isConcatIn = in_array($concatEqual, [self::CONCAT_IN, self::CONCAT_NOT_IN]);

		if (is_array($value)) {
			if (!$isConcatIn) {
				$value = $value[0];
			} else {
				$w = [];
				foreach ($value as $oneValue) {
					$oneValue = tString::encodeValue($oneValue);
					if (is_string($oneValue)) {
						$oneValue = '"' . $oneValue . '"';
					}

					$w[] = $oneValue;
				}
				$value = '(' . implode(',', $w) . ')';
			}
		}

		return $nameColumn . ' ' . $concatEqual . ' ' . $value;
	}

	private function compileWhereValues($input)
	{
		if (!is_array($input)) {
			return $input;
		}

		$w = [];
		foreach ($input as $index => $data) {
			$w[] = $this->compileWhereValue($data);
		}

		return implode(' AND ', $w);
	}

	public function where($input, $args = [], $add = false, $or = false)
	{
		$input = $this->compileWhereValues($input);

		if (!empty($args)) {
			foreach ($args as $index => $arg) {
				$arg = tString::encodeValue($arg);
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

	public function update(string $table, array $sliceValues, array $condition, array $excludeCheck = [])
	{
		list($names, $values) = $this->getSliceValues($sliceValues, $excludeCheck);

		$query = 'UPDATE ' . $table . ' SET' . PAGE_EOL;
		$upd = [];
		foreach ($names as $index => $name) {
			$upd[] = $table . '.' . $name . ' = ' . $values[$index];
		}
		$query .= implode(', ', $upd) . PAGE_EOL;

		$where = [];
		foreach ($condition as $condName => $condValue) {
			if (is_array($condValue)) {
				// ['id', 'IN', $data['ids']]
				if (empty($condValue) || count($condValue) < 2) {
					continue;
				}

				$nameColumn = $table . '.' . $condValue[0];
				$concatEqual = self::CONCAT_EQUAL;
				$values = $condValue[1];
				if (isset($condValue[2])) {
					$concatEqual = $this->getDefaultConcatEqual($condValue[1]);
					$values = $condValue[2];
				}

				$ids = [];
				if (is_array($values)) {
					foreach ($values as $id) {
						$ids[] = tString::encodeValue($id);
					}
				} else {
					$ids[] = $values;
				}

				$where[] = $nameColumn . ' ' . $concatEqual . ' (' . implode(',', $ids) . ')';
			} else {
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
			$condValue = is_int($condValue) ? (int)$condValue : '"' . tString::encodeString($condValue, true) . '"';
			$where[] = $table . '.' . $condName . ' = ' . $condValue;
		}
		$query .= 'WHERE ' . implode(' AND ', $where);

		return $this->deleteRow($query);
	}

	private function getSliceValues(array $sliceValues, array $excludeCheck = [])
	{
		$names = $values = [];
		foreach ($sliceValues as $index => $value) {
			$names[] = $index;

			if (!empty($excludeCheck) && in_array($index, $excludeCheck)) {
				$value = is_numeric($value) ? (int)$value : "'" . $value . "'";
			} else {
				$value = is_numeric($value) ? (int)$value : "'" . tString::encodeString($value, true) . "'";
			}

			$values[] = $value;
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
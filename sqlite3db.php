<?php
/*
 *============================================================
 * Program Name: Easy Jia Content Management System (EJCMS)
 * Web Address:  http://www.ejcms.com
 * Copyright:    2011 EJCMS.com. All rights reserved
 * Author:       TengLin
 * File Name:    SQLite3DB.php
 *============================================================
 */

class SQLite3DB extends SQLite3 {
	public $dbfile = "";
	public $prefix = "";
	protected $columns = "*";
	protected $_join = [];
	protected $_where = [];
	protected $_orderby = [];

	public function __construct($dbfile = "", $prefix = "") {
		if (empty($this->dbfile)) {
			if (empty($dbfile)) {
				exit("Please write sqlite database address!");
			} else {
				$this->open($dbfile);
			}
		} else {
			$this->open($this->dbfile);
		}
		if (!empty($prefix)) {
			$this->prefix = $prefix;
		}
	}

	public function create($table, $data) {
		if (count($data) == count($data, 1)) {
			$id = $this->insert($table, $data);
		} else {
			$id = $this->insertMulti($table, $data);
		}
		$this->reset();
		if ($id) {
			return $id;
		} else {
			return $this->lastErrorMsg();
		}
	}

	public function update($table, $data, $array = []) {
		$set = [];
		if (!empty($array)) {
			foreach ($array as $k => $v) {
				$this->where($k, $v);
			}
		}
		$query = "UPDATE " . $this->prefix . $table . " SET ";
		foreach ($data as $k => $v) {
			$set[] = "[" . $k . "] = '" . $v . "'";
		}
		$query .= implode(", ", $set) . $this->_where() . ";";
		$result = $this->exec($query);
		$this->reset();
		return $result;
	}

	public function delete($table, $array = []) {
		if (!empty($array)) {
			foreach ($array as $k => $v) {
				$this->where($k, $v);
			}
		}
		$query = "DELETE FROM [" . $this->prefix . $table . "]" . $this->_where() . ";";
		$result = $this->exec($query);
		$this->reset();
		return $result;
	}

	public function detail($table, $array = []) {
		if (!empty($array)) {
			foreach ($array as $k => $v) {
				$this->where($k, $v);
			}
		}
		if (is_array($table)) {
			if ($table["join"]["table"]) {
				$this->_db->join($table["join"]["table"], $table["join"]["condition"], $table["join"]["type"]);
			} else {
				foreach ($table["join"] as $v) {
					$this->_db->join($v["table"], $v["condition"], $v["type"]);
				}
			}
			$tables["table"] = $table["table"];
		} else {
			$tables["table"] = $table;
		}
		$result = $this->querySingle($this->_select($tables["table"]) . $this->_join() . $this->_where(), true);
		$this->reset();
		return $result;
	}

	public function items($table, $limit = null, &$callback = [], $columns = "*") {
		if (is_array($table)) {
			if ($table["join"]["table"]) {
				$this->join($table["join"]["table"], $table["join"]["condition"], $table["join"]["type"]);
			} else {
				foreach ($table["join"] as $v) {
					$this->join($v["table"], $v["condition"], $v["type"]);
				}
			}
			$tables["table"] = $table["table"];
		} else {
			$tables["table"] = $table;
		}
		$this->columns = $columns;
		$result = $this->query($this->_select($tables["table"]) . $this->_join() . $this->_where() . $this->_orderby() . $this->_limit($limit));
		$totalCount = $this->querySingle($this->_select($tables["table"], "count(*)") . $this->_join() . $this->_where());
		$this->reset();
		$callback = $this->_pageinfo($totalCount, $limit);
		$row = [];
		while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
			$row[] = $res;
		}
		return $row;
	}

	public function join($table, $condition, $type = "INNER") {
		$_join[] = $type . " JOIN " . $table . " ON " . $condition;
	}

	public function where($prop, $value = "", $operator = "=", $cond = "AND") {
		$where = [];
		if (count($this->_where) == 0) {
			$cond = "";
		}
		$this->_where[] = [$cond, $prop, $operator, $value];
		return $this;
	}

	public function orwhere($prop, $value = "", $operator = "=") {
		return $this->where($prop, $value, $operator, "OR");
	}

	public function orderby($field, $direction = "DESC") {
		$this->_orderby[$field] = $direction;
	}

	protected function reset() {
		$this->columns = "*";
		$this->_join = [];
		$this->_where = [];
		$this->_orderby = [];
	}

	protected function insert($table, $data, &$error = "") {
		$sql = "INSERT INTO [" . $this->prefix . $table . "] ";
		$sql .= "(" . implode(", ", array_keys($data)) . ")";
		$sql .= " VALUES ";
		$sql .= "('" . implode("', '", array_values($data)) . "');";
		if ($this->exec($sql)) {
			return $this->lastInsertRowID();
		}
		$error = $this->lastErrorMsg();
		return false;
	}

	protected function insertMulti($table, $data) {
		$ids = [];
		$this->exec("begin;");
		foreach ($data as $v) {
			$ids[] = $this->insert($table, $v);
		}
		$this->exec("commit;");
		return $ids;
	}

	protected function _select($table, $columns = "") {
		if (empty($columns)) {
			$columns = $this->columns;
		}
		return "SELECT " . $columns . " FROM [" . $this->prefix . $table . "]";
	}

	protected function _join() {
		if (empty($this->_join)) {
			return;
		}
		return " " . implode(" ", $this->_join);
	}

	protected function _where() {
		if (empty($this->_where)) {
			return;
		}
		$build = " WHERE";
		foreach ($this->_where as $cond) {
			list($concat, $varName, $operator, $val) = $cond;
			$build .= " " . $concat . " " . $varName . " ";
			switch (strtolower($operator)) {
			case "not in":
			case "in":
				$build .= $operator . " ('" . implode("', '", $val) . "')";
				break;
			case "not between":
			case "between":
				$build .= $operator . " " . sprintf("%u AND %u", $val[0], $val[1]);
				break;
			case "not exists":
			case "exists":
				$build .= $operator . " (" . $val . ")";
				break;
			default:
				$build .= $operator . " '" . $val . "'";
			}
		}
		return $build;
	}

	protected function _orderby() {
		if (empty($this->_orderby)) {
			return;
		}
		$build = " ORDER BY ";
		foreach ($this->_orderby as $prop => $value) {
			if (strtolower(str_replace(" ", "", $prop)) == "rand()") {
				$build .= "rand(), ";
			} else {
				$build .= $prop . " " . $value . ", ";
			}
		}
		return rtrim($build, ", ") . " ";
	}

	protected function _limit($numRows) {
		if (empty($numRows)) {
			return;
		}
		if (is_array($numRows)) {
			if ($numRows[0] < 1) {
				$numRows[0] = 1;
			}
			return " LIMIT " . (int) $numRows[0] . ", " . (int) $numRows[1];
		} else {
			return " LIMIT " . (int) $numRows;
		}
	}

	protected function _pageinfo($totalCount, $numRows) {
		$pageinfo = [];
		if (empty($numRows)) {
			$pageinfo["page"] = 1;
		} else {
			if (is_array($limit)) {
				$pageinfo["page"] = $numRows[0];
				$pageinfo["pageSize"] = $numRows[1];
				$pageinfo["totalPages"] = ceil($totalCount / $numRows[1]);
			} else {
				$pageinfo["page"] = 1;
				$pageinfo["pageSize"] = $numRows;
				$pageinfo["totalPages"] = ceil($totalCount / $numRows);
			}
		}
		$pageinfo["totalCount"] = $totalCount;
		return $pageinfo;
	}
}

<?php
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */
?>
<?php

class Default_Model_Filter {
	
	public $_expr;
	public $fltstr;
	protected $_limit;
	protected $_offset;
	protected $_orderBy;
	public $_fields;
	public $_fieldTypes;
	public $_table;
	protected $_joins;
	protected $_privateJoins;
	private $_dialect;

	public function __construct() {
		$this->clear();
		$this->_fields = array();
		$this->_expr = null;
		$this->_joins = array();
		$this->_privateJoins = array();
		$this->fltstr = '';
		$this->setDialect(0);
	}

	public function setDialect($d) {
		$this->_dialect = $d;
	}

	public function __set($item, $val) {
		if (strtolower($item) == "limit") {
			$this->limit($val);
		} elseif (strtolower($item) == "offset") {
			$this->offset($val);
		} elseif (strtolower($item) == "orbderby") {
			$this->orderBy($val);
		} else {
			$this->item($item)->equals($val);
		}
	}

	public function __get($item) {
		if (strtolower($item) == "limit") {
			return $this->getLimit();
		} elseif (strtolower($item) == "offset") {
			return $this->getOffset();
		} elseif (strtolower($item) == "orderby") {
			return $this->getOrderBy();
		} elseif (strtolower($item) == "privatejoins") {
			return $this->getPrivateJoins();
		} elseif (strtolower($item) == "joins") {
			return $this->getJoins();
		} else {
			return $this->item($item);
		}
	}

	public function getPrivateJoins() {
		return $this->_privateJoins;
	}

	public function getJoins() {
		return $this->_joins;
	}

	public function item($i) {
		if ( (in_array($i,$this->_fields)) || ($i === "any" ) ) {
			$f = new Default_Model_FilterItem($i, $this);
		} else {
			$found = false;
			foreach ($this->_fields as $ii) {
				//debug_log('/^' . $ii . '$/');
				if (preg_match('/^' . $ii . '$/', $i)) {
					$found = true;
					$f = new Default_Model_FilterItem($i, $this);
					break;
				}
			}
			if (!$found) {
				$f = new Default_Model_FilterItem($this->_fields[0], $this);
			}			
		}
		$f->setDialect($this->_dialect);
		return $f;
	}

	public function clear() {
		$this->_limit = null;
		$this->_offset = null;
		$this->_orderBy = null;
		$this->_expr = "";
	}

	public function orderBy($v) {
		if (is_array($v)) {
			$r = array();
			foreach ($v as $i) $r[] = $this->_table.".".$i;
			$this->_orderBy = $r;
		} else {
                    $this->_orderBy = $this->_table.".".$v;
                }
	}

	public function limit($v, $o = null) {
		$this->_limit = $v;
		if ($o !== null) {
			$this->_offset = $o;
		}
	}

	public function offset($v) {
		$this->_offset = $v;
	}

	public function getLimit() {
		return $this->_limit;
	}

	public function getOffset() {
		return $this->_offset;
	}

	public function getOrderBy() {
		return $this->_orderBy;
	}

	public function expr() {
		$s = $this->_expr;
//		if ( ( $s != '' ) && ( $this->_table != '') ) {
//			$s = $this->_table.".id IN (SELECT ".$this->_table.".id FROM ".$this->_table." WHERE $s)";
//		}
		return $s;
	}

	public function setExpr($v) {
		$this->_expr = $v;
		return $this;
	}

	public function chain($f, $r = "AND", $private = false) {
		if ( is_array($this->_expr) ) {
			for ($i = 0; $i < count($this->_expr); $i++) {
				if ( ($this->_expr[$i] != '') ) {
					$this->_expr[$i] = "(".$this->_expr[$i].") $r (".$f->_expr.")";
				} else {
					$this->_expr[$i] = $f->_expr;
				}
			}
		} else {
			if ( $this->_expr != '') {
				$this->_expr = "(".$this->expr().") $r (".$f->expr().")";
			} else {
				$this->_expr = $f->expr();
			}
		}
		$j = $this->_joins;
		$j[] = $f->_table;
		$this->_joins = array_merge($j, $f->joins);
		if ( $private ) {
			$j = $this->_privateJoins;
			$j[] = $f->_table;
			$this->_privateJoins = array_merge($j, $f->privateJoins);
		} else {
			$this->_privateJoins = array_merge($this->_privateJoins, $f->privateJoins);
		}

		return $this;
	}

}

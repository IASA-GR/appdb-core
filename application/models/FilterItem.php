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

class Default_Model_FilterItem {

	protected $_ancestor;
	protected $_field;
	protected $_expr;
	protected $_tmpTable = null;
	private $_dialect;
	public $_escape_seq;
	private $_ilike;
	private $_rlike;
	private $_charCast;

	public function __construct($field, Default_Model_Filter $ancestor) {
		$this->_ancestor = $ancestor;
		$this->_field = $field;
		$this->setDialect(0);
	}

	public function __call($m, $a) {
		if ( $m == "and" ) {
			return $this->_and($a[0]);
		} elseif ( $m == "or" ) {
			return $this->_or($a[0]);
		} elseif ( substr($m,0,3) == "not" ) {
			$funcName = substr($m,3);
			$coalesce = false;
			$sanitize = true;
			if (is_array($a)) {
				if (count($a) >=2) $coalese = $a[1];
				if (count($a) >=3) $sanitize = $a[2];
			}
			$this->$funcName($a[0], $coalesce, $sanitize);
			$this->_expr = "(NOT (".$this->_expr."))";
			$this->_ancestor->_expr = $this->_expr;
			return $this;
		}
	}

	public function setDialect($dialect) {
		if (is_string($dialect)) {
			switch($dialect) {
			case "pgsql": $d = 0; break;
			case "mysql": $d = 1; break;
			default: $d = 0;
			}
		} elseif (is_numeric($dialect)) {
			$d = $dialect;
		} else {
			$d = 0;
		}
		$this->_dialect = $d;
		switch ($this->_dialect) {
		case 0:
			$this->_escape_seq = "E";
			$this->_ilike = "LIKE";
			$this->_rlike = "~*";
			$this->_charCast = "TEXT";
			break;
		case 1:
			$this->_escape_seq = "";
			$this->_ilike = "LIKE";
			$this->_rlike = "RLIKE";
			$this->_charCast = "CHAR";
			break;
		}
	}

	private function _escape($val) {
		switch($this->_dialect) {
		case 0:
			$val = pg_escape_string($val);
			break;
		case 1:
			$val = mysql_real_escape_string($val);
			break;
		default:
			$val = str_replace("'", "''", $val);
		}
		return $val;
	}

	private function checkAny($val, $op, $coalesce = false) {
		if ($this->_field == "any") {
			if ( in_array("any.any", $this->_ancestor->_fields) )  {
				$f = new Default_Model_FilterItem("any.any", $this->_ancestor);
				$f->$op($val, $coalesce);
				$this->_expr = "(".$f->expr().")";
			} else {
				$s = array();
				foreach ($this->_ancestor->_fields as $field) {
					if ( (array_key_exists($field, $this->_ancestor->_fieldTypes)) && ( ($this->_ancestor->_fieldTypes[$field] == 'string') || ($this->_ancestor->_fieldTypes[$field] == 'string[]') || ($field == "id") ) ) {
						if ( $field !== "any.any" ) {
							$f = new Default_Model_FilterItem($field, $this->_ancestor);
							$f->$op($val, $coalesce);
							$s[] = $f->expr();
						}
					}
				}
				$this->_expr = "(".implode($s," OR ").")";
			}
			return true;
		} else {
			return false;
		}
	}

	private function numcomp($op, $val, $coalesce = false, $sanitize = true) {
		$doText = false;
		if ( $this->_field != 'any' ) {
			if ( $sanitize && ( ($this->_ancestor->_fieldTypes[$this->_field] == 'string') || ($this->_ancestor->_fieldTypes[$this->_field] == 'string[]') || (! is_numeric($val)) ) ) {	//sanitization; switch to text comparison if either the field or the value are non-numeric
				if ( is_string($val) ) {
					$val = str_replace("'","’",$val);
					$val = str_replace('"','”',$val);
					$val = $this->_escape_seq . "'".$this->_escape($val)."'";
				}
				$doText = true;
			}
		}
		switch($op) {
		case "gt":
			$op2 = ">";
			break;
		case "ge":
			$op2 = ">=";
			break;
		case "lt":
			$op2 = "<";
			break;
		case "le":
			$op2 = "<=";
			break;
		}
		if ( ! $this->checkAny($val, $op, $coalesce) ) {
			if ($val === null) {
				$val = 0;
			} elseif ( ($val === true) || ($val === false) ) {
				$val = (int)$val;
			} elseif ( (strtolower($val) === "true") ) {
				$val = 1;
			} elseif  ( (strtolower($val) === "false") ) {
				$val = 0;
			} else {
				if ($coalesce) {
					if ($this->_dialect == 0) {
						$this->_expr = "(COALESCE(".$this->_ancestor->_table.".".$this->_field.($doText?"::text":"")." , " . ($doText?"'0'":"0") . ") $op2 ".$val.")";
					} elseif ($this->_dialect == 1) {
						if ($dotext) {
							$this->_expr = "(COALESCE(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."), '0') $op2 ".$val.")";
						} else {
							$this->_expr = "(COALESCE(".$this->_ancestor->_table.".".$this->_field." ,0) $op2 ".$val.")";
						}
					}
				} else {
					if ($this->_dialect == 0) {
						$this->_expr = "(".$this->_ancestor->_table.".".$this->_field.($doText?"::text":"")." $op2 ".$val.")";
					} elseif ($this->_dialect == 1) {
						if ($dotext) {
							$this->_expr = "(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast.") $op2 ".$val.")";
						} else {
							$this->_expr = "(".$this->_ancestor->_table.".".$this->_field." $op2 ".$val.")";
						}
					}
				}
			}
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function gt($val, $coalesce = false, $sanitize = true) {
		return $this->numcomp("gt", $val, $coalesce, $sanitize);
	}

	public function lt($val, $coalesce = false, $sanitize = true) {
		return $this->numcomp("lt", $val, $coalesce, $sanitize);
	}

	public function ge($val, $coalesce = false, $sanitize = true) {
		return $this->numcomp("ge", $val, $coalesce, $sanitize);
	}

	public function le($val, $coalesce = false, $sanitize = true) {
		return $this->numcomp("le", $val, $coalesce, $sanitize);
	}

	public function contains($val, $coalesce = false, $sanitize = true) {
		if ( ! $this->checkAny($val, "contains", $coalesce) ) {
			$this->_expr = "(".$this->_ancestor->_table.".".$this->_field." @> ".$val.")";
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function numequals($val, $coalesce = false) {
		if ( ! $this->checkAny($val, "equals", $coalesce) ) {
			if (($val === null) || ($val === "NULL")) {
				$this->_expr = "(".$this->_ancestor->_table.".".$this->_field." IS NULL)";
			} elseif ( ($val === true) || ($val === false) || (strtolower($val) === "true") || (strtolower($val) === "false") ) {
				$this->_expr = "((".$this->_ancestor->_table.".".$this->_field.") IS ".((($val === true) || (strtolower($val) === "true")) ? "TRUE":"FALSE").")";
			} else {
//				if ( (array_key_exists($this->_field, $this->_ancestor->_fieldTypes)) && ( $this->_ancestor->_fieldTypes[$this->_field] == 'string' ) ) {
//					$this->_expr = "(".$this->_ancestor->_table.".".$this->_field." = '".$val."')";
//				} else {
				if ($coalesce) 
					$this->_expr = "(COALESCE(".$this->_ancestor->_table.".".$this->_field." ,0) = ".$val.")";
				else
					$this->_expr = "(".$this->_ancestor->_table.".".$this->_field."  = ".$val.")";
//				}
			}
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function equals($val, $coalesce = false) {
		if ( (array_key_exists($this->_field, $this->_ancestor->_fieldTypes)) && ( substr($this->_ancestor->_fieldTypes[$this->_field],-2) == '[]' ) ) $isArray = true; else $isArray = false;
		if ( is_string($val) ) {
			$val = str_replace("'","’",$val);
			$val = str_replace('"','”',$val);
		}
		if ( ! $this->checkAny($val, "equals", $coalesce) ) {
			if (($val === null) || ($val === "NULL")) {
				$this->_expr = "(".$this->_ancestor->_table.".".$this->_field." IS NULL)";
			} elseif ( ($val === true) || ($val === false) || (strtolower($val) === "true") || (strtolower($val) === "false") ) {
				$this->_expr = "((".$this->_ancestor->_table.".".$this->_field.") IS ".((($val === true) || (strtolower($val) === "true")) ? "TRUE":"FALSE").")";
			} else {
				if ($coalesce) {
					if ( $isArray ) {
						$this->_expr = "(" . $this->_escape_seq . "'".$this->_escape($val)."' = ANY((COALESCE(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."[]),'{}'))))";
					} else {
						$this->_expr = "(COALESCE(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."),'') = ".$this->_escape_seq."'".$this->_escape($val)."')";
					}
				} else {
					if ( $isArray ) {
						$this->_expr = "(" . $this->_escape_seq . "'" .$this->_escape($val)."' = ANY(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."[])))";
					} else {
						$this->_expr = "(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast.") = ".$this->_escape_seq."'".$this->_escape($val)."')";
					}
				}
			}
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function like($val, $coalesce = false) {
		if ( (array_key_exists($this->_field, $this->_ancestor->_fieldTypes)) && ( substr($this->_ancestor->_fieldTypes[$this->_field],-2) == '[]' ) ) $isArray = true; else $isArray = false;
		if ( is_string($val) ) {
			$val = str_replace("'","’",$val);
			$val = str_replace('"','”',$val);
		}
		if (! $this->checkAny($val, "like", $coalesce)) {
			if ($coalesce) {
				if ( $isArray ) {
					$this->_expr = "(" . $this->_escape_seq . "'".$this->_escape($val)."' ~~~ ANY(COALESCE(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."[]),'')))"; 	
				} else {
					$this->_expr = "(COALESCE(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."),'') LIKE " . $this->_escape_seq . "'".$this->_escape($val)."')"; 	
				}
			} else {
				if ( $isArray ) {
					$this->_expr = "(". $this->_escape_seq ."'".$this->_escape($val)."' ~~~ ANY(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."[])))"; 
				} else {
					$this->_expr = "(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast.") LIKE ".$this->_escape_seq."'".$this->_escape($val)."')"; 
				}
			}
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function ilike($val, $coalesce = false) {
		if ( $val === '%%' ) return $this;

		if ( $val !== "NULL" ) $val = strtolower($val);
		if ( $this->_field === 'any.any' ) {			
			$lower = "";	// these tables' data are guaranteed to be in lowercase from creation. avoid costly LOWER function
		} else {
			$lower = "LOWER";
		}
		if ( (array_key_exists($this->_field, $this->_ancestor->_fieldTypes)) && ( substr($this->_ancestor->_fieldTypes[$this->_field],-2) == '[]' ) ) $isArray = true; else $isArray = false;
		if ( is_string($val) ) {
			$val = str_replace("'","’",$val);
			$val = str_replace('"','”',$val);
		}
		if (! $this->checkAny($val, "ilike", $coalesce)) {
			if ( (array_key_exists($this->_field, $this->_ancestor->_fieldTypes)) && ( substr($this->_ancestor->_fieldTypes[$this->_field],-2) == '[]' ) ) $isArray = true;
			if ( ($val === null) || ($val === "NULL") ) {
				$this->_expr = "(".$this->_ancestor->_table.".".$this->_field." IS NULL)";
			} elseif ( ($val === true) || ($val === false) || (strtolower($val) === "true") || (strtolower($val) === "false") ) {
				$this->_expr = "((".$this->_ancestor->_table.".".$this->_field.") IS ".((($val === true) || (strtolower($val) === "true")) ? "TRUE":"FALSE").")";
			} else {
				if ($coalesce) {
					if ( $isArray ) {
						$this->_expr = "(".$this->_escape_seq."'".$val."' ~~~@ ANY(COALESCE(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."[]),'')))";
					} else {
						$this->_expr = "(COALESCE(".$lower."(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast.")),'') ".$this->_ilike." ".$this->_escape_seq."'".$val."')";
					}
				} else {
					if ( $isArray ) {
						$this->_expr = "(".$this->_escape_seq."'".$val."' ~~~@ ANY (CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."[])))";
					} else {
						$this->_expr = "(".$lower."(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast.")) ".$this->_ilike." ".$this->_escape_seq."'".$val."')";
					}
				}
			}
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function between($val, $coalesce = false) {
		if (($this->_ancestor->_fieldTypes[$this->_field] == 'integer') || ($this->_dialect != 0)) {
			if (! $this->checkAny($val, "between", $coalesce)) $this->_expr = "(".$this->_ancestor->_table.".".$this->_field." BETWEEN ".$val[0]." AND ".$val[1].")";
		} else {
			if (! $this->checkAny($val, "between", $coalesce)) $this->_expr = "(EXTRACT(EPOCH FROM ".$this->_ancestor->_table.".".$this->_field.") BETWEEN ".$val[0]." AND ".$val[1].")";
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function any($val, $coalesce = false, $sanitize = true) {
		if (! $this->checkAny($val, "any", $coalesce)) {
			$s='';
			if (is_array($val)) {
				if ($sanitize) {
					$s = "'".implode($val,"', '")."'";
				} else {
					$s = implode($val);
				}
			} else {
				if ($sanitize) {
					$s = "'".$val."'";
				} else {
					$s = $val;
				}
			}
			if ( ( $s == "''" ) && ($this->_ancestor->_fieldTypes[$this->_field] == 'integer') ) $s = 'NULL';
			$this->_expr = "(".$this->_ancestor->_table.".".$this->_field." = ANY(".$s."))";
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function in($val, $coalesce = false, $sanitize = true) {
		if (! $this->checkAny($val, "in", $coalesce)) {
			$s='';
			if (is_array($val)) {
				if ($sanitize) {
					$s = "'".implode($val,"', '")."'";
				} else {
					$s = implode($val);
				}
			} else {
				if ($sanitize) {
					$s = explode(",", $val);
					$s = "'".implode($s,"', '")."'";
				} else {
					$s = $val;
				}
			}
			if ( ( $s == "''" ) && ($this->_ancestor->_fieldTypes[$this->_field] == 'integer') ) $s = 'NULL';
			$this->_expr = "(".$this->_ancestor->_table.".".$this->_field." IN (".$s."))";
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function hasbit($val, $coalesce = false) {
		if (! $this->checkAny($val, "hasbit", $coalesce)) {
			$this->_expr = "(CAST(".$this->_ancestor->_table.".".$this->_field." & ".$val." AS boolean))";
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function matches($val, $coalesce = false) {
		if ( is_string($val) ) {
			$val = str_replace("'","’",$val);
			$val = str_replace('"','”',$val);
		}
		if (! $this->checkAny($val, "matches", $coalesce)) {
			if ($coalesce) 
				$this->_expr = "(COALESCE(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."),'') ".$this->_rlike." ".$this->_escape_seq."'".$this->_escape($val)."')";
			else
				$this->_expr = "(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast.") ".$this->_rlike." ".$this->_escape_seq."'".$this->_escape($val)."')";
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function soundsLike($val, $coalesce = false) {
		if ( is_string($val) ) {
			$val = str_replace("'","’",$val);
			$val = str_replace('"','”',$val);
		}
		if ($this->_dialect == 0) {
			if (! $this->checkAny($val, "soundsLike", $coalesce)) $this->_expr = "(soundex(".$this->_escape_seq."'".$this->_escape($val)."') = ANY(soundexx(string_to_array(COALESCE(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."),''),' '))))";
		} elseif ($this->_dialect == 1) {
			if (! $this->checkAny($val, "soundsLike", $coalesce)) $this->_expr = "(soundex(".$this->_escape_seq."'".$this->_escape($val)."') = soundex(COALESCE(CAST(".$this->_ancestor->_table.".".$this->_field." AS ".$this->_charCast."),'')))";
		}
		$this->_ancestor->_expr = $this->expr();
		return $this;
	}

	public function expr() {
		$s = $this->_expr;
		$s = str_replace($this->_ancestor->_table.".EXTRACT","EXTRACT",$s);
		$s = str_replace($this->_ancestor->_table.".(SELECT","(SELECT",$s);
		$s = str_replace("###THETABLE###", $this->_ancestor->_table, $s);
		//debug_log($s);
		return $s;
	}

	public function field() {
		return $this->_field;
	}

	public function _and($val) {
		if ( $this->_expr == '') {
			$this->_expr = $val->expr();
		} else {
			$this->_expr = "(".$this->_expr.") AND (".$val->expr().")";
		}
		$this->_ancestor->_expr = $this->_expr;
		return $this;
	}

	public function _or($val) {
		if ( $this->_expr == '') {
			$this->_expr = $val->expr();
		} else {
			$this->_expr = "(".$this->_expr.") OR (".$val->expr().")";
		}
		$this->_ancestor->_expr = $this->_expr;
		return $this;
	}

	public function _not($val) {
		$this->_ancestor->not($val);
		return $this;
	}

}

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
namespace Application\Model;

class AROProperty {
	private $_basename;
	private $_parent;
	protected $_dbcol;
	protected $_name;
	protected $_fko;
	protected $_value;
	protected $_fvalue;
	protected $_isset;
	
	
	public function __construct($parent, $dbcol, $name = null, $fko = null, $defval = null) {
		$this->_basename = 'AROProperty';
		$this->_parent = $parent;
		$this->_dbcol = $dbcol;
		if (is_null($name)) {
			$this->_name = $dbcol;
		} else {
			$this->_name = $name;
		}
		$this->_fko = $fko;
		if (is_null($this->_fko)) {
			$this->_isset = false;
		}
	}
	
	public function __destruct() {
		unset($this->_parent);
		if (! is_null($this->_fko)) {
			unset($this->_fvalue);
		}
	}
	
	public function __toString() {
		return "" . $this->getValue();
	}
	
	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
				throw new Exception("Invalid " . $this->_basename . " property: '$name'");
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
				throw new Exception("Invalid " . $this->_basename . " property: '$name'");
		}
		return $this->$method();
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getDBCol() {
		return $this->_dbcol;
	}
	
	public function getValue() {
		if (! is_null($this->_fko)) { 
			if (! $this->_isset) {				
				if (is_array($this->_value)) {
					$this->_fvalue = array();
					foreach($this->_value as $v) {
						if ($v != "") {
							$type = "Application\\Model\\" . $this->_fko;
							$vv = new $type;
							//error_log("Calling $type::find($v) from " . $this->_basename . "::getValue for FKO " . $this->_fko);
							$vv->find($v);
							$this->_fvalue[] = $vv;
						} else {
							error_log("ID(" . $this->_parent->id . "): property value " . $this->_name . " for FKO " . $this->_fko . " is unset!!!");
						}
					}
				} else {	
					if ($this->_value != "") {
						$type = "Application\\Model\\" . $this->_fko;
						$this->_fvalue = new $type;
						//error_log("Calling $type::find(" . $this->_value . ") from " . $this->_basename . "::getValue for FKO " . $this->_fko);
						$this->_fvalue->find($this->_value);
					} else {
						error_log("ID(" . $this->_parent->id . "): property value " . $this->_name . " for FKO " . $this->_fko . " is unset!!!");
					}
				}				
				$this->_isset = true;
			}
			//error_log("Returning FKO value from " . $this->_basename . "::getValue for FKO " . $this->_fko);
			return $this->_fvalue;
		} else {
			//error_log("Returning scalar value from " . $this->_basename . "::getValue for property $this->_name");
			return $this->_value;
		}
	}
	
	public function setValue($value) {
		/*if (is_array($value)) {
			error_log("AROProperty: setting value of ". $this->name . "//" . $this->dbcol . " to array");
		} else {
			error_log("AROProperty: setting value of ". $this->name . "//" . $this->dbcol . " to $value");
		}*/
		$this->_value = $value;
		//if (is_null($this->_fko)) {
			//$this->_value = $value;
		//} else {
			//throw new Exception("Cannot set FKO property " . $this->_name . " of ARO object");
		//}
		return $this;
	}
	
	public function getIsFKO() {
		return ! is_null($this->_fko);
	}
}

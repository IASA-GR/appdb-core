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

class AROItem {
	protected $_mapper;
	protected $_properties;
	protected $_basename;
	protected $_baseitemname;
	
	public function __construct() {
		$this->_properties = array();
	}
	
	public function __destruct() {
		//error_log(var_export($this->toXML(), true));
		unset($this->_mapper);
		if (is_array($this->_properties)) {
			for ($i = count($this->_properties) - 1; $i >= 0; --$i) {
				unset($this->_properties[$i]);
			}
		}
		unset($this->_properties);
	}
	
	public function getProperties() {
		return $this->_properties;
	}
	
	public function __set($name,$value) {	
		//error_log("Calling setter function for $name");
		$found = false;
		foreach ($this->_properties as &$prop) {
			if (((strtolower($name) == strtolower($prop->name)) && (! $prop->isFKO)) || (strtolower($name) == strtolower($prop->dbcol))) {
				if (is_string($value)) {
					$value = str_replace("'", "’", $value);
					$value = str_replace('"', '”', $value);
				}
				$prop->value = $value;
				$found = true;
				//break; // don't break, we might need to set the FKO inner value as well
			}
		}
		if (! $found) {
			$method = 'set' . $name;
			if (('mapper' == $name) || !method_exists($this, $method)) {
				throw new Exception("Invalid " . $this->_baseitemname . " property: '$name'");
			}
			if (is_string($value)) {
				$value = str_replace("'", "’", $value);
				$value = str_replace('"', '”', $value);
			}
			$this->$method($value);			
		} else {
			return $this;
		}
	}
        
	public function __get($name){
		$found = false;
		foreach ($this->_properties as &$prop) {
			//error_log(strtolower($name) . " =? " . strtolower($prop->name));
			if (strtolower($name) == strtolower($prop->name)) {			
				$found = true;
				$ret = $prop->value;
				if (is_string($ret)) {
					$ret = str_replace("'","’", $ret);
					$ret = str_replace('"','”', $ret);
				}
				break;
			}
		}
//		if (! $found) {
//		foreach ($this->_properties as &$prop) {
//		&& (! $prop->isFKO)) || (strtolower($name) == strtolower($prop->dbcol))) {
//		}
		if (! $found) {
			$method = 'get' . $name;
			if (('mapper' == $name) || !method_exists($this, $method)) {
					throw new Exception("Invalid " . $this->_baseitemname . " property: '$name'");
			}
			$ret = $this->$method();
			if (is_string($ret)) {
					$ret = str_replace("'","’", $ret);
					$ret = str_replace('"','”', $ret);
			}
			return $ret;			
		} else {
			return $ret;
		}		
	}
	

	public function __call($name, $args) {
		//error_log("__call: $name");
		if (! method_exists($this, $name)) {
			foreach ($this->_properties as &$prop) {
				if ((("set" . (strtolower($prop->name)) == strtolower($name)) && (! $prop->isFKO)) || (("set" . strtolower($prop->dbcol)) == strtolower($name))) {
					//error_log("call: calling __set($prop[0])");
					return call_user_func_array(array(&$this, "__set"), array_merge(array($prop->name), $args));
				//} elseif ((("get" . (strtolower($prop->name)) == strtolower($name)) && (! $prop->isFKO)) || (("get" . strtolower($prop->dbcol)) == strtolower($name))) {
				} elseif ("get" . (strtolower($prop->name)) == strtolower($name)) {
					//error_log("call: calling __get($prop[0])");
					return call_user_func_array(array(&$this, "__get"), array_merge(array($prop->name), $args));
				} elseif (method_exists($this, $name)) {
					//error_log("call: calling method $name");
					return call_user_func_array(array(&$this, $name), $args);
				}/* elseif (method_exists($this, "set" . $name)) {
					//error_log("call: calling method $name");
					return call_user_func_array(array(&$this, "set" . $name), $args);
				} elseif (method_exists($this, "get" . $name)) {
					//error_log("call: calling method $name");
					return call_user_func_array(array(&$this, "get" . $name), $args);
				}*/
			}
			throw new Exception("Call to undefined method $name");
		}
	}

	public function setOptions(array $options) {
		$methods = get_class_methods($this);
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if (in_array($method, $methods)) {
				$this->$method($value);
			}
		}
		return $this;
	}
	
	public function setMapper($mapper) {
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper() {
		if (null === $this->_mapper) {
			$type = "Application\\Model\\" . $this->_basename . "Mapper";
			$this->setMapper(new $type);
		}
		return $this->_mapper;
	}

	public function save() {
		$this->getMapper()->save($this);
	}

	public function find($id) {
		$this->getMapper()->find($id, $this);
		return $this;
	}

	public function fetchAll($args = null) {
		return $this->getMapper()->fetchAll($args);
	}

	public function toXML($recursive=false) {
		$XML = "<" . $this->_baseitemname . ">\n";
		foreach ($this->_properties as &$prop) {
			if (! $prop->isFKO) {
				if (is_array($prop->value)) {
					foreach ($prop->value as $p) {
						if (isset($p)) {
							$XML .= '<' . $prop->name . '>' . recode_string("utf8..xml", $p). '</' . $prop->name . '>' . "\n";
						}
					}
				} else {
					//if (isset($prop->value)) {
						$sval = "";
						if ($prop->value === true) {
							$sval = "true";
						} elseif ($prop->value === false) {
							$sval = "false";
						} else {
							$sval = recode_string("utf8..xml", $prop->value);
						}
						if ($sval != "") {
							$XML .= '<' . $prop->name . '>' . $sval . '</' . $prop->name . '>' . "\n";
						}
					//}
				}
			} else {
				if ($recursive) {
					$XML .= $prop->value->toXML();
				}
			}
		}
		$XML .= "</" . $this->_baseitemname . ">\n";
		return $XML;
	}

}

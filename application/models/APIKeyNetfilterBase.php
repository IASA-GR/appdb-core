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
class Default_Model_APIKeyNetfilterBase
{
	protected $_mapper;
	protected $_netfilter;
	protected $_keyID;
	protected $_key;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid APIKeyNetfilter property: '$name'");
		}
		if ( is_string($value) ) {
			$value = str_replace("'","’",$value);
			$value = str_replace('"','”',$value);
		}
		$this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid APIKeyNetfilter property: '$name'");
		}
		$ret = $this->$method();
		if ( is_string($ret) ) {
			$ret= str_replace("'","’",$ret);
			$ret = str_replace('"','”',$ret);
		}
		return $ret;
	}

	public function setOptions(array $options)
	{
		$methods = get_class_methods($this);
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if (in_array($method, $methods)) {
				$this->$method($value);
			}
		}
		return $this;
	}

	public function setNetfilter($value)
	{
		/* if ( $value === null ) {
			$this->_netfilter = 'NULL';
		} else */ $this->_netfilter = $value;
		return $this;
	}

	public function getNetfilter()
	{
		return $this->_netfilter;
	}

	public function setKeyID($value)
	{
		/* if ( $value === null ) {
			$this->_keyID = 'NULL';
		} else */ $this->_keyID = $value;
		return $this;
	}

	public function getKeyID()
	{
		return $this->_keyID;
	}

	public function getKey()
	{
		if ( $this->_key === null ) {
			$APIKeys = new Default_Model_APIKeys();
			$APIKeys->filter->id->equals($this->getKeyID());
			if ($APIKeys->count() > 0) $this->_key = $APIKeys->items[0];
		}
		return $this->_key;
	}

	public function setKey($value)
	{
		if ( $value === null ) {
			$this->setKeyID(null);
		} else {
			$this->setKeyID($value->getId());
		}
	}


	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_APIKeyNetfiltersMapper());
		}
		return $this->_mapper;
	}

	public function save()
	{
		$this->getMapper()->save($this);
	}

	public function find($id)
	{
		$this->getMapper()->find($id, $this);
		return $this;
	}

	public function fetchAll($args = null)
	{
		return $this->getMapper()->fetchAll($args);
	}

	public function toXML($recursive=false)
	{
		$XML = "<APIKeyNetfilter>\n";
		if ($this->_netfilter !== null) $XML .= "<netfilter>".recode_string("utf8..xml",$this->_netfilter)."</netfilter>\n";
		if ($this->_keyID !== null) $XML .= "<keyID>".$this->_keyID."</keyID>\n";
		if ( $recursive ) if ( $this->_key === null ) $this->getKey();
		if ( ! ($this->_key === null) ) $XML .= $this->_key->toXML();
		$XML .= "</APIKeyNetfilter>\n";
		return $XML;
	}
}

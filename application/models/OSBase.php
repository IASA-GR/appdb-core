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
class Default_Model_OSBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_OSFamilyID;
	protected $_OSFamily;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid OS property: '$name'");
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
			throw new Exception("Invalid OS property: '$name'");
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

	public function setId($value)
	{
		/* if ( $value === null ) {
			$this->_id = 'NULL';
		} else */ $this->_id = $value;
		return $this;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function setName($value)
	{
		/* if ( $value === null ) {
			$this->_name = 'NULL';
		} else */ $this->_name = $value;
		return $this;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function setOSFamilyID($value)
	{
		/* if ( $value === null ) {
			$this->_OSFamilyID = 'NULL';
		} else */ $this->_OSFamilyID = $value;
		return $this;
	}

	public function getOSFamilyID()
	{
		return $this->_OSFamilyID;
	}

	public function getOSFamily()
	{
		if ( $this->_OSFamily === null ) {
			$OSFamilies = new Default_Model_OSFamilies();
			$OSFamilies->filter->id->equals($this->getOSFamilyID());
			if ($OSFamilies->count() > 0) $this->_OSFamily = $OSFamilies->items[0];
		}
		return $this->_OSFamily;
	}

	public function setOSFamily($value)
	{
		if ( $value === null ) {
			$this->setOSFamilyID(null);
		} else {
			$this->setOSFamilyID($value->getId());
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
			$this->setMapper(new Default_Model_OSesMapper());
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
		$XML = "<OS>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_OSFamilyID !== null) $XML .= "<OSFamilyID>".$this->_OSFamilyID."</OSFamilyID>\n";
		if ( $recursive ) if ( $this->_OSFamily === null ) $this->getOSFamily();
		if ( ! ($this->_OSFamily === null) ) $XML .= $this->_OSFamily->toXML();
		$XML .= "</OS>\n";
		return $XML;
	}
}

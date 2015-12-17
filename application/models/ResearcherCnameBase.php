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
class Default_Model_ResearcherCnameBase
{
	protected $_mapper;
	protected $_id;
	protected $_created;
	protected $_enabled;
	protected $_isprimary;
	protected $_value;
	protected $_researcherID;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid ResearcherCname property: '$name'");
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
			throw new Exception("Invalid ResearcherCname property: '$name'");
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

	public function setCreated($value)
	{
		/* if ( $value === null ) {
			$this->_created = 'NULL';
		} else */ $this->_created = $value;
		return $this;
	}

	public function getCreated()
	{
		return $this->_created;
	}

	public function setEnabled($value)
	{
		/* if ( $value === null ) {
			$this->_enabled = 'NULL';
		} else */ $this->_enabled = $value;
		return $this;
	}

	public function getEnabled()
	{
		$v = $this->_enabled;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setIsprimary($value)
	{
		/* if ( $value === null ) {
			$this->_isprimary = 'NULL';
		} else */ $this->_isprimary = $value;
		return $this;
	}

	public function getIsprimary()
	{
		$v = $this->_isprimary;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setValue($value)
	{
		/* if ( $value === null ) {
			$this->_value = 'NULL';
		} else */ $this->_value = $value;
		return $this;
	}

	public function getValue()
	{
		return $this->_value;
	}

	public function setResearcherID($value)
	{
		/* if ( $value === null ) {
			$this->_researcherID = 'NULL';
		} else */ $this->_researcherID = $value;
		return $this;
	}

	public function getResearcherID()
	{
		return $this->_researcherID;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_ResearcherCnamesMapper());
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
		$XML = "<ResearcherCname>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_created !== null) $XML .= "<created>".recode_string("utf8..xml",$this->_created)."</created>\n";
		if ($this->_enabled !== null) $XML .= "<enabled>".$this->_enabled."</enabled>\n";
		if ($this->_isprimary !== null) $XML .= "<isprimary>".$this->_isprimary."</isprimary>\n";
		if ($this->_value !== null) $XML .= "<value>".recode_string("utf8..xml",$this->_value)."</value>\n";
		if ($this->_researcherID !== null) $XML .= "<researcherID>".$this->_researcherID."</researcherID>\n";
		$XML .= "</ResearcherCname>\n";
		return $XML;
	}
}

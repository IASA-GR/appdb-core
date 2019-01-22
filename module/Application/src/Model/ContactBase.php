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
class Default_Model_ContactBase
{
	protected $_mapper;
	protected $_id;
	protected $_researcherID;
	protected $_researcher;
	protected $_contactTypeID;
	protected $_contactType;
	protected $_data;
	protected $_isPrimary;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Contact property: '$name'");
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
			throw new Exception("Invalid Contact property: '$name'");
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

	public function getResearcher()
	{
		if ( $this->_researcher === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getResearcherID());
			if ($Researchers->count() > 0) $this->_researcher = $Researchers->items[0];
		}
		return $this->_researcher;
	}

	public function setResearcher($value)
	{
		if ( $value === null ) {
			$this->setResearcherID(null);
		} else {
			$this->setResearcherID($value->getId());
		}
	}


	public function setContactTypeID($value)
	{
		/* if ( $value === null ) {
			$this->_contactTypeID = 'NULL';
		} else */ $this->_contactTypeID = $value;
		return $this;
	}

	public function getContactTypeID()
	{
		return $this->_contactTypeID;
	}

	public function getContactType()
	{
		if ( $this->_contactType === null ) {
			$ContactTypes = new Default_Model_ContactTypes();
			$ContactTypes->filter->id->equals($this->getContactTypeID());
			if ($ContactTypes->count() > 0) $this->_contactType = $ContactTypes->items[0];
		}
		return $this->_contactType;
	}

	public function setContactType($value)
	{
		if ( $value === null ) {
			$this->setContactTypeID(null);
		} else {
			$this->setContactTypeID($value->getId());
		}
	}


	public function setData($value)
	{
		/* if ( $value === null ) {
			$this->_data = 'NULL';
		} else */ $this->_data = $value;
		return $this;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function setIsPrimary($value)
	{
		/* if ( $value === null ) {
			$this->_isPrimary = 'NULL';
		} else */ $this->_isPrimary = $value;
		return $this;
	}

	public function getIsPrimary()
	{
		$v = $this->_isPrimary;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
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
			$this->setMapper(new Default_Model_ContactsMapper());
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
		$XML = "<Contact>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_researcherID !== null) $XML .= "<researcherID>".$this->_researcherID."</researcherID>\n";
		if ( $recursive ) if ( $this->_researcher === null ) $this->getResearcher();
		if ( ! ($this->_researcher === null) ) $XML .= $this->_researcher->toXML();
		if ($this->_contactTypeID !== null) $XML .= "<contactTypeID>".$this->_contactTypeID."</contactTypeID>\n";
		if ( $recursive ) if ( $this->_contactType === null ) $this->getContactType();
		if ( ! ($this->_contactType === null) ) $XML .= $this->_contactType->toXML();
		if ($this->_data !== null) $XML .= "<data>".recode_string("utf8..xml",$this->_data)."</data>\n";
		if ($this->_isPrimary !== null) $XML .= "<isPrimary>".$this->_isPrimary."</isPrimary>\n";
		$XML .= "</Contact>\n";
		return $XML;
	}
}

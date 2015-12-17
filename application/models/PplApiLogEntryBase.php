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
class Default_Model_PplApiLogEntryBase
{
	protected $_mapper;
	protected $_id;
	protected $_pplID;
	protected $_person;
	protected $_timestamp;
	protected $_researcherID;
	protected $_researcher;
	protected $_source;
	protected $_ip;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid PplApiLogEntry property: '$name'");
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
			throw new Exception("Invalid PplApiLogEntry property: '$name'");
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

	public function setPplID($value)
	{
		/* if ( $value === null ) {
			$this->_pplID = 'NULL';
		} else */ $this->_pplID = $value;
		return $this;
	}

	public function getPplID()
	{
		return $this->_pplID;
	}

	public function getPerson()
	{
		if ( $this->_person === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getPplID());
			if ($Researchers->count() > 0) $this->_person = $Researchers->items[0];
		}
		return $this->_person;
	}

	public function setPerson($value)
	{
		if ( $value === null ) {
			$this->setPplID(null);
		} else {
			$this->setPplID($value->getId());
		}
	}


	public function setTimestamp($value)
	{
		/* if ( $value === null ) {
			$this->_timestamp = 'NULL';
		} else */ $this->_timestamp = $value;
		return $this;
	}

	public function getTimestamp()
	{
		return $this->_timestamp;
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


	public function setSource($value)
	{
		/* if ( $value === null ) {
			$this->_source = 'NULL';
		} else */ $this->_source = $value;
		return $this;
	}

	public function getSource()
	{
		return $this->_source;
	}

	public function setIp($value)
	{
		/* if ( $value === null ) {
			$this->_ip = 'NULL';
		} else */ $this->_ip = $value;
		return $this;
	}

	public function getIp()
	{
		return $this->_ip;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_PplApiLogMapper());
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
		$XML = "<PplApiLogEntry>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_pplID !== null) $XML .= "<pplID>".$this->_pplID."</pplID>\n";
		if ( $recursive ) if ( $this->_person === null ) $this->getPerson();
		if ( ! ($this->_person === null) ) $XML .= $this->_person->toXML();
		if ($this->_timestamp !== null) $XML .= "<timestamp>".recode_string("utf8..xml",$this->_timestamp)."</timestamp>\n";
		if ($this->_researcherID !== null) $XML .= "<researcherID>".$this->_researcherID."</researcherID>\n";
		if ( $recursive ) if ( $this->_researcher === null ) $this->getResearcher();
		if ( ! ($this->_researcher === null) ) $XML .= $this->_researcher->toXML();
		if ($this->_source !== null) $XML .= "<source>".$this->_source."</source>\n";
		if ($this->_ip !== null) $XML .= "<ip>".recode_string("utf8..xml",$this->_ip)."</ip>\n";
		$XML .= "</PplApiLogEntry>\n";
		return $XML;
	}
}

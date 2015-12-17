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
class Default_Model_HarvestContactPersonBase
{
	protected $_mapper;
	protected $_id;
	protected $_identifier;
	protected $_fullName;
	protected $_email;
	protected $_phone;
	protected $_fax;
	protected $_researcherID;
	protected $_researcher;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid HarvestContactPerson property: '$name'");
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
			throw new Exception("Invalid HarvestContactPerson property: '$name'");
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

	public function setIdentifier($value)
	{
		/* if ( $value === null ) {
			$this->_identifier = 'NULL';
		} else */ $this->_identifier = $value;
		return $this;
	}

	public function getIdentifier()
	{
		return $this->_identifier;
	}

	public function setFullName($value)
	{
		/* if ( $value === null ) {
			$this->_fullName = 'NULL';
		} else */ $this->_fullName = $value;
		return $this;
	}

	public function getFullName()
	{
		return $this->_fullName;
	}

	public function setEmail($value)
	{
		/* if ( $value === null ) {
			$this->_email = 'NULL';
		} else */ $this->_email = $value;
		return $this;
	}

	public function getEmail()
	{
		return $this->_email;
	}

	public function setPhone($value)
	{
		/* if ( $value === null ) {
			$this->_phone = 'NULL';
		} else */ $this->_phone = $value;
		return $this;
	}

	public function getPhone()
	{
		return $this->_phone;
	}

	public function setFax($value)
	{
		/* if ( $value === null ) {
			$this->_fax = 'NULL';
		} else */ $this->_fax = $value;
		return $this;
	}

	public function getFax()
	{
		return $this->_fax;
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


	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_HarvestContactPersonsMapper());
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
		$XML = "<HarvestContactPerson>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_identifier !== null) $XML .= "<identifier>".recode_string("utf8..xml",$this->_identifier)."</identifier>\n";
		if ($this->_fullName !== null) $XML .= "<fullName>".recode_string("utf8..xml",$this->_fullName)."</fullName>\n";
		if ($this->_email !== null) $XML .= "<email>".recode_string("utf8..xml",$this->_email)."</email>\n";
		if ($this->_phone !== null) $XML .= "<phone>".recode_string("utf8..xml",$this->_phone)."</phone>\n";
		if ($this->_fax !== null) $XML .= "<fax>".$this->_fax."</fax>\n";
		if ($this->_researcherID !== null) $XML .= "<researcherID>".$this->_researcherID."</researcherID>\n";
		if ( $recursive ) if ( $this->_researcher === null ) $this->getResearcher();
		if ( ! ($this->_researcher === null) ) $XML .= $this->_researcher->toXML();
		$XML .= "</HarvestContactPerson>\n";
		return $XML;
	}
}

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
class Default_Model_VOContactBase
{
	protected $_mapper;
	protected $_voID;
	protected $_VO;
	protected $_researcherID;
	protected $_researcher;
	protected $_role;
	protected $_email;
	protected $_name;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VOContact property: '$name'");
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
			throw new Exception("Invalid VOContact property: '$name'");
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

	public function setVoID($value)
	{
		/* if ( $value === null ) {
			$this->_voID = 'NULL';
		} else */ $this->_voID = $value;
		return $this;
	}

	public function getVoID()
	{
		return $this->_voID;
	}

	public function getVO()
	{
		if ( $this->_VO === null ) {
			$VOs = new Default_Model_VOs();
			$VOs->filter->id->equals($this->getVoID());
			if ($VOs->count() > 0) $this->_VO = $VOs->items[0];
		}
		return $this->_VO;
	}

	public function setVO($value)
	{
		if ( $value === null ) {
			$this->setVoID(null);
		} else {
			$this->setVoID($value->getId());
		}
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


	public function setRole($value)
	{
		/* if ( $value === null ) {
			$this->_role = 'NULL';
		} else */ $this->_role = $value;
		return $this;
	}

	public function getRole()
	{
		return $this->_role;
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

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_VOContactsMapper());
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
		$XML = "<VOContact>\n";
		if ($this->_voID !== null) $XML .= "<voID>".$this->_voID."</voID>\n";
		if ( $recursive ) if ( $this->_VO === null ) $this->getVO();
		if ( ! ($this->_VO === null) ) $XML .= $this->_VO->toXML();
		if ($this->_researcherID !== null) $XML .= "<researcherID>".recode_string("utf8..xml",$this->_researcherID)."</researcherID>\n";
		if ( $recursive ) if ( $this->_researcher === null ) $this->getResearcher();
		if ( ! ($this->_researcher === null) ) $XML .= $this->_researcher->toXML();
		if ($this->_role !== null) $XML .= "<role>".recode_string("utf8..xml",$this->_role)."</role>\n";
		if ($this->_email !== null) $XML .= "<email>".recode_string("utf8..xml",$this->_email)."</email>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		$XML .= "</VOContact>\n";
		return $XML;
	}
}

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
class Default_Model_PplDelInfoBase
{
	protected $_mapper;
	protected $_id;
	protected $_researcherID;
	protected $_researcher;
	protected $_deletedBy;
	protected $_deleter;
	protected $_deletedOn;
	protected $_roleID;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid PplDelInfo property: '$name'");
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
			throw new Exception("Invalid PplDelInfo property: '$name'");
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


	public function setDeletedBy($value)
	{
		/* if ( $value === null ) {
			$this->_deletedBy = 'NULL';
		} else */ $this->_deletedBy = $value;
		return $this;
	}

	public function getDeletedBy()
	{
		return $this->_deletedBy;
	}

	public function getDeleter()
	{
		if ( $this->_deleter === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getDeletedBy());
			if ($Researchers->count() > 0) $this->_deleter = $Researchers->items[0];
		}
		return $this->_deleter;
	}

	public function setDeleter($value)
	{
		if ( $value === null ) {
			$this->setDeletedBy(null);
		} else {
			$this->setDeletedBy($value->getId());
		}
	}


	public function setDeletedOn($value)
	{
		/* if ( $value === null ) {
			$this->_deletedOn = 'NULL';
		} else */ $this->_deletedOn = $value;
		return $this;
	}

	public function getDeletedOn()
	{
		return $this->_deletedOn;
	}

	public function setRoleID($value)
	{
		/* if ( $value === null ) {
			$this->_roleID = 'NULL';
		} else */ $this->_roleID = $value;
		return $this;
	}

	public function getRoleID()
	{
		return $this->_roleID;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_PplDelInfosMapper());
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
		$XML = "<PplDelInfo>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_researcherID !== null) $XML .= "<researcherID>".$this->_researcherID."</researcherID>\n";
		if ( $recursive ) if ( $this->_researcher === null ) $this->getResearcher();
		if ( ! ($this->_researcher === null) ) $XML .= $this->_researcher->toXML();
		if ($this->_deletedBy !== null) $XML .= "<deletedBy>".$this->_deletedBy."</deletedBy>\n";
		if ( $recursive ) if ( $this->_deleter === null ) $this->getDeleter();
		if ( ! ($this->_deleter === null) ) $XML .= $this->_deleter->toXML();
		if ($this->_deletedOn !== null) $XML .= "<deletedOn>".recode_string("utf8..xml",$this->_deletedOn)."</deletedOn>\n";
		if ($this->_roleID !== null) $XML .= "<roleID>".$this->_roleID."</roleID>\n";
		$XML .= "</PplDelInfo>\n";
		return $XML;
	}
}

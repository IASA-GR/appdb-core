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
class Default_Model_UserAccountBase
{
	protected $_mapper;
	protected $_id;
	protected $_researcherID;
	protected $_researcher;
	protected $_accountID;
	protected $_accountTypeID;
	protected $_accountType;
	protected $_accountname;
	protected $_stateID;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid UserAccount property: '$name'");
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
			throw new Exception("Invalid UserAccount property: '$name'");
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


	public function setAccountID($value)
	{
		/* if ( $value === null ) {
			$this->_accountID = 'NULL';
		} else */ $this->_accountID = $value;
		return $this;
	}

	public function getAccountID()
	{
		return $this->_accountID;
	}

	public function setAccountTypeID($value)
	{
		/* if ( $value === null ) {
			$this->_accountTypeID = 'NULL';
		} else */ $this->_accountTypeID = $value;
		return $this;
	}

	public function getAccountTypeID()
	{
		return $this->_accountTypeID;
	}

	public function setAccountname($value)
	{
		/* if ( $value === null ) {
			$this->_accountname = 'NULL';
		} else */ $this->_accountname = $value;
		return $this;
	}

	public function getAccountname()
	{
		return $this->_accountname;
	}

	public function setStateID($value)
	{
		/* if ( $value === null ) {
			$this->_stateID = 'NULL';
		} else */ $this->_stateID = $value;
		return $this;
	}

	public function getStateID()
	{
		return $this->_stateID;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_UserAccountsMapper());
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
		$XML = "<UserAccount>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_researcherID !== null) $XML .= "<researcherID>".recode_string("utf8..xml",$this->_researcherID)."</researcherID>\n";
		if ( $recursive ) if ( $this->_researcher === null ) $this->getResearcher();
		if ( ! ($this->_researcher === null) ) $XML .= $this->_researcher->toXML();
		if ($this->_accountID !== null) $XML .= "<accountID>".recode_string("utf8..xml",$this->_accountID)."</accountID>\n";
		if ($this->_accountTypeID !== null) $XML .= "<accountTypeID>".recode_string("utf8..xml",$this->_accountTypeID)."</accountTypeID>\n";
		if ( $recursive ) if ( $this->_accountType === null ) $this->getAccountType();
		if ( ! ($this->_accountType === null) ) $XML .= $this->_accountType->toXML();
		if ($this->_accountname !== null) $XML .= "<accountname>".recode_string("utf8..xml",$this->_accountname)."</accountname>\n";
		if ($this->_stateID !== null) $XML .= "<stateID>".$this->_stateID."</stateID>\n";
		$XML .= "</UserAccount>\n";
		return $XML;
	}
}

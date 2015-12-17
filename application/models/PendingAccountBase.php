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
class Default_Model_PendingAccountBase
{
	protected $_mapper;
	protected $_id;
	protected $_code;
	protected $_researcherID;
	protected $_accountID;
	protected $_accountType;
	protected $_accountName;
	protected $_resolved;
	protected $_resolvedon;
	protected $_addedon;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid PendingAccount property: '$name'");
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
			throw new Exception("Invalid PendingAccount property: '$name'");
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

	public function setCode($value)
	{
		/* if ( $value === null ) {
			$this->_code = 'NULL';
		} else */ $this->_code = $value;
		return $this;
	}

	public function getCode()
	{
		return $this->_code;
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

	public function setAccountType($value)
	{
		/* if ( $value === null ) {
			$this->_accountType = 'NULL';
		} else */ $this->_accountType = $value;
		return $this;
	}

	public function getAccountType()
	{
		return $this->_accountType;
	}

	public function setAccountName($value)
	{
		/* if ( $value === null ) {
			$this->_accountName = 'NULL';
		} else */ $this->_accountName = $value;
		return $this;
	}

	public function getAccountName()
	{
		return $this->_accountName;
	}

	public function setResolved($value)
	{
		/* if ( $value === null ) {
			$this->_resolved = 'NULL';
		} else */ $this->_resolved = $value;
		return $this;
	}

	public function getResolved()
	{
		$v = $this->_resolved;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setResolvedon($value)
	{
		/* if ( $value === null ) {
			$this->_resolvedon = 'NULL';
		} else */ $this->_resolvedon = $value;
		return $this;
	}

	public function getResolvedon()
	{
		return $this->_resolvedon;
	}

	public function setAddedon($value)
	{
		/* if ( $value === null ) {
			$this->_addedon = 'NULL';
		} else */ $this->_addedon = $value;
		return $this;
	}

	public function getAddedon()
	{
		return $this->_addedon;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_PendingAccountsMapper());
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
		$XML = "<PendingAccount>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_code !== null) $XML .= "<code>".$this->_code."</code>\n";
		if ($this->_researcherID !== null) $XML .= "<researcherID>".$this->_researcherID."</researcherID>\n";
		if ($this->_accountID !== null) $XML .= "<accountID>".recode_string("utf8..xml",$this->_accountID)."</accountID>\n";
		if ($this->_accountType !== null) $XML .= "<accountType>".$this->_accountType."</accountType>\n";
		if ($this->_accountName !== null) $XML .= "<accountName>".recode_string("utf8..xml",$this->_accountName)."</accountName>\n";
		if ($this->_resolved !== null) $XML .= "<resolved>".$this->_resolved."</resolved>\n";
		if ($this->_resolvedon !== null) $XML .= "<resolvedon>".recode_string("utf8..xml",$this->_resolvedon)."</resolvedon>\n";
		if ($this->_addedon !== null) $XML .= "<addedon>".recode_string("utf8..xml",$this->_addedon)."</addedon>\n";
		$XML .= "</PendingAccount>\n";
		return $XML;
	}
}

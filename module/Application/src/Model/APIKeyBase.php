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
class Default_Model_APIKeyBase
{
	protected $_mapper;
	protected $_key;
	protected $_authMethods;
	protected $_ownerID;
	protected $_owner;
	protected $_createdOn;
	protected $_id;
	protected $_sysAccountID;
	protected $_sysAccount;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid APIKey property: '$name'");
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
			throw new Exception("Invalid APIKey property: '$name'");
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

	public function setKey($value)
	{
		/* if ( $value === null ) {
			$this->_key = 'NULL';
		} else */ $this->_key = $value;
		return $this;
	}

	public function getKey()
	{
		return $this->_key;
	}

	public function setAuthMethods($value)
	{
		/* if ( $value === null ) {
			$this->_authMethods = 'NULL';
		} else */ $this->_authMethods = $value;
		return $this;
	}

	public function getAuthMethods()
	{
		return $this->_authMethods;
	}

	public function setOwnerID($value)
	{
		/* if ( $value === null ) {
			$this->_ownerID = 'NULL';
		} else */ $this->_ownerID = $value;
		return $this;
	}

	public function getOwnerID()
	{
		return $this->_ownerID;
	}

	public function getOwner()
	{
		if ( $this->_owner === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getOwnerID());
			if ($Researchers->count() > 0) $this->_owner = $Researchers->items[0];
		}
		return $this->_owner;
	}

	public function setOwner($value)
	{
		if ( $value === null ) {
			$this->setOwnerID(null);
		} else {
			$this->setOwnerID($value->getId());
		}
	}


	public function setCreatedOn($value)
	{
		/* if ( $value === null ) {
			$this->_createdOn = 'NULL';
		} else */ $this->_createdOn = $value;
		return $this;
	}

	public function getCreatedOn()
	{
		return $this->_createdOn;
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

	public function setSysAccountID($value)
	{
		/* if ( $value === null ) {
			$this->_sysAccountID = 'NULL';
		} else */ $this->_sysAccountID = $value;
		return $this;
	}

	public function getSysAccountID()
	{
		return $this->_sysAccountID;
	}

	public function getSysAccount()
	{
		if ( $this->_sysAccount === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getSysAccountID());
			if ($Researchers->count() > 0) $this->_sysAccount = $Researchers->items[0];
		}
		return $this->_sysAccount;
	}

	public function setSysAccount($value)
	{
		if ( $value === null ) {
			$this->setSysAccountID(null);
		} else {
			$this->setSysAccountID($value->getId());
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
			$this->setMapper(new Default_Model_APIKeysMapper());
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
		$XML = "<APIKey>\n";
		if ($this->_key !== null) $XML .= "<key>".recode_string("utf8..xml",$this->_key)."</key>\n";
		if ($this->_authMethods !== null) $XML .= "<authMethods>".$this->_authMethods."</authMethods>\n";
		if ($this->_ownerID !== null) $XML .= "<ownerID>".$this->_ownerID."</ownerID>\n";
		if ( $recursive ) if ( $this->_owner === null ) $this->getOwner();
		if ( ! ($this->_owner === null) ) $XML .= $this->_owner->toXML();
		if ($this->_createdOn !== null) $XML .= "<createdOn>".recode_string("utf8..xml",$this->_createdOn)."</createdOn>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_sysAccountID !== null) $XML .= "<sysAccountID>".$this->_sysAccountID."</sysAccountID>\n";
		if ( $recursive ) if ( $this->_sysAccount === null ) $this->getSysAccount();
		if ( ! ($this->_sysAccount === null) ) $XML .= $this->_sysAccount->toXML();
		$XML .= "</APIKey>\n";
		return $XML;
	}
}

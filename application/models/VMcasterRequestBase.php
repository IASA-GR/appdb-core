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
class Default_Model_VMcasterRequestBase
{
	protected $_mapper;
	protected $_id;
	protected $_status;
	protected $_username;
	protected $_password;
	protected $_authType;
	protected $_errorMessage;
	protected $_insertedOn;
	protected $_lastSubmitted;
	protected $_ip;
	protected $_inputVMIL;
	protected $_producedXML;
	protected $_appID;
	protected $_application;
	protected $_action;
	protected $_entity;
	protected $_ldapSn;
	protected $_ldapDn;
	protected $_ldapEmail;
	protected $_ldapDisplayname;
	protected $_ldapCn;
	protected $_ldapUsercertificatesubject;
	protected $_ldapGivenname;
	protected $_rID;
	protected $_uID;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VMcasterRequest property: '$name'");
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
			throw new Exception("Invalid VMcasterRequest property: '$name'");
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

	public function setStatus($value)
	{
		/* if ( $value === null ) {
			$this->_status = 'NULL';
		} else */ $this->_status = $value;
		return $this;
	}

	public function getStatus()
	{
		return $this->_status;
	}

	public function setUsername($value)
	{
		/* if ( $value === null ) {
			$this->_username = 'NULL';
		} else */ $this->_username = $value;
		return $this;
	}

	public function getUsername()
	{
		return $this->_username;
	}

	public function setPassword($value)
	{
		/* if ( $value === null ) {
			$this->_password = 'NULL';
		} else */ $this->_password = $value;
		return $this;
	}

	public function getPassword()
	{
		return $this->_password;
	}

	public function setAuthType($value)
	{
		/* if ( $value === null ) {
			$this->_authType = 'NULL';
		} else */ $this->_authType = $value;
		return $this;
	}

	public function getAuthType()
	{
		return $this->_authType;
	}

	public function setErrorMessage($value)
	{
		/* if ( $value === null ) {
			$this->_errorMessage = 'NULL';
		} else */ $this->_errorMessage = $value;
		return $this;
	}

	public function getErrorMessage()
	{
		return $this->_errorMessage;
	}

	public function setInsertedOn($value)
	{
		/* if ( $value === null ) {
			$this->_insertedOn = 'NULL';
		} else */ $this->_insertedOn = $value;
		return $this;
	}

	public function getInsertedOn()
	{
		return $this->_insertedOn;
	}

	public function setLastSubmitted($value)
	{
		/* if ( $value === null ) {
			$this->_lastSubmitted = 'NULL';
		} else */ $this->_lastSubmitted = $value;
		return $this;
	}

	public function getLastSubmitted()
	{
		return $this->_lastSubmitted;
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

	public function setInputVMIL($value)
	{
		/* if ( $value === null ) {
			$this->_inputVMIL = 'NULL';
		} else */ $this->_inputVMIL = $value;
		return $this;
	}

	public function getInputVMIL()
	{
		return $this->_inputVMIL;
	}

	public function setProducedXML($value)
	{
		/* if ( $value === null ) {
			$this->_producedXML = 'NULL';
		} else */ $this->_producedXML = $value;
		return $this;
	}

	public function getProducedXML()
	{
		return $this->_producedXML;
	}

	public function setAppID($value)
	{
		/* if ( $value === null ) {
			$this->_appID = 'NULL';
		} else */ $this->_appID = $value;
		return $this;
	}

	public function getAppID()
	{
		return $this->_appID;
	}

	public function getApplication()
	{
		if ( $this->_application === null ) {
			$Applications = new Default_Model_Applications();
			$Applications->filter->id->equals($this->getAppID());
			if ($Applications->count() > 0) $this->_application = $Applications->items[0];
		}
		return $this->_application;
	}

	public function setApplication($value)
	{
		if ( $value === null ) {
			$this->setAppID(null);
		} else {
			$this->setAppID($value->getId());
		}
	}


	public function setAction($value)
	{
		/* if ( $value === null ) {
			$this->_action = 'NULL';
		} else */ $this->_action = $value;
		return $this;
	}

	public function getAction()
	{
		return $this->_action;
	}

	public function setEntity($value)
	{
		/* if ( $value === null ) {
			$this->_entity = 'NULL';
		} else */ $this->_entity = $value;
		return $this;
	}

	public function getEntity()
	{
		return $this->_entity;
	}

	public function setLdapSn($value)
	{
		/* if ( $value === null ) {
			$this->_ldapSn = 'NULL';
		} else */ $this->_ldapSn = $value;
		return $this;
	}

	public function getLdapSn()
	{
		return $this->_ldapSn;
	}

	public function setLdapDn($value)
	{
		/* if ( $value === null ) {
			$this->_ldapDn = 'NULL';
		} else */ $this->_ldapDn = $value;
		return $this;
	}

	public function getLdapDn()
	{
		return $this->_ldapDn;
	}

	public function setLdapEmail($value)
	{
		/* if ( $value === null ) {
			$this->_ldapEmail = 'NULL';
		} else */ $this->_ldapEmail = $value;
		return $this;
	}

	public function getLdapEmail()
	{
		return $this->_ldapEmail;
	}

	public function setLdapDisplayname($value)
	{
		/* if ( $value === null ) {
			$this->_ldapDisplayname = 'NULL';
		} else */ $this->_ldapDisplayname = $value;
		return $this;
	}

	public function getLdapDisplayname()
	{
		return $this->_ldapDisplayname;
	}

	public function setLdapCn($value)
	{
		/* if ( $value === null ) {
			$this->_ldapCn = 'NULL';
		} else */ $this->_ldapCn = $value;
		return $this;
	}

	public function getLdapCn()
	{
		return $this->_ldapCn;
	}

	public function setLdapUsercertificatesubject($value)
	{
		/* if ( $value === null ) {
			$this->_ldapUsercertificatesubject = 'NULL';
		} else */ $this->_ldapUsercertificatesubject = $value;
		return $this;
	}

	public function getLdapUsercertificatesubject()
	{
		return $this->_ldapUsercertificatesubject;
	}

	public function setLdapGivenname($value)
	{
		/* if ( $value === null ) {
			$this->_ldapGivenname = 'NULL';
		} else */ $this->_ldapGivenname = $value;
		return $this;
	}

	public function getLdapGivenname()
	{
		return $this->_ldapGivenname;
	}

	public function setRID($value)
	{
		/* if ( $value === null ) {
			$this->_rID = 'NULL';
		} else */ $this->_rID = $value;
		return $this;
	}

	public function getRID()
	{
		return $this->_rID;
	}

	public function setUID($value)
	{
		/* if ( $value === null ) {
			$this->_uID = 'NULL';
		} else */ $this->_uID = $value;
		return $this;
	}

	public function getUID()
	{
		return $this->_uID;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_VMcasterRequestsMapper());
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
		$XML = "<VMcasterRequest>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_status !== null) $XML .= "<status>".recode_string("utf8..xml",$this->_status)."</status>\n";
		if ($this->_username !== null) $XML .= "<username>".recode_string("utf8..xml",$this->_username)."</username>\n";
		if ($this->_password !== null) $XML .= "<password>".recode_string("utf8..xml",$this->_password)."</password>\n";
		if ($this->_authType !== null) $XML .= "<authType>".recode_string("utf8..xml",$this->_authType)."</authType>\n";
		if ($this->_errorMessage !== null) $XML .= "<errorMessage>".recode_string("utf8..xml",$this->_errorMessage)."</errorMessage>\n";
		if ($this->_insertedOn !== null) $XML .= "<insertedOn>".recode_string("utf8..xml",$this->_insertedOn)."</insertedOn>\n";
		if ($this->_lastSubmitted !== null) $XML .= "<lastSubmitted>".recode_string("utf8..xml",$this->_lastSubmitted)."</lastSubmitted>\n";
		if ($this->_ip !== null) $XML .= "<ip>".recode_string("utf8..xml",$this->_ip)."</ip>\n";
		if ($this->_inputVMIL !== null) $XML .= "<inputVMIL>".recode_string("utf8..xml",$this->_inputVMIL)."</inputVMIL>\n";
		if ($this->_producedXML !== null) $XML .= "<producedXML>".recode_string("utf8..xml",$this->_producedXML)."</producedXML>\n";
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ( $recursive ) if ( $this->_application === null ) $this->getApplication();
		if ( ! ($this->_application === null) ) $XML .= $this->_application->toXML();
		if ($this->_action !== null) $XML .= "<action>".recode_string("utf8..xml",$this->_action)."</action>\n";
		if ($this->_entity !== null) $XML .= "<entity>".recode_string("utf8..xml",$this->_entity)."</entity>\n";
		if ($this->_ldapSn !== null) $XML .= "<ldapSn>".recode_string("utf8..xml",$this->_ldapSn)."</ldapSn>\n";
		if ($this->_ldapDn !== null) $XML .= "<ldapDn>".recode_string("utf8..xml",$this->_ldapDn)."</ldapDn>\n";
		if ($this->_ldapEmail !== null) $XML .= "<ldapEmail>".recode_string("utf8..xml",$this->_ldapEmail)."</ldapEmail>\n";
		if ($this->_ldapDisplayname !== null) $XML .= "<ldapDisplayname>".recode_string("utf8..xml",$this->_ldapDisplayname)."</ldapDisplayname>\n";
		if ($this->_ldapCn !== null) $XML .= "<ldapCn>".recode_string("utf8..xml",$this->_ldapCn)."</ldapCn>\n";
		if ($this->_ldapUsercertificatesubject !== null) $XML .= "<ldapUsercertificatesubject>".recode_string("utf8..xml",$this->_ldapUsercertificatesubject)."</ldapUsercertificatesubject>\n";
		if ($this->_ldapGivenname !== null) $XML .= "<ldapGivenname>".recode_string("utf8..xml",$this->_ldapGivenname)."</ldapGivenname>\n";
		if ($this->_rID !== null) $XML .= "<rID>".$this->_rID."</rID>\n";
		if ($this->_uID !== null) $XML .= "<uID>".$this->_uID."</uID>\n";
		$XML .= "</VMcasterRequest>\n";
		return $XML;
	}
}

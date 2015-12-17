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
class Default_Model_VOMSBase
{
	protected $_mapper;
	protected $_voID;
	protected $_VO;
	protected $_hostname;
	protected $_httpsPort;
	protected $_vomsesPort;
	protected $_isAdmin;
	protected $_memberListUrl;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VOMS property: '$name'");
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
			throw new Exception("Invalid VOMS property: '$name'");
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


	public function setHostname($value)
	{
		/* if ( $value === null ) {
			$this->_hostname = 'NULL';
		} else */ $this->_hostname = $value;
		return $this;
	}

	public function getHostname()
	{
		return $this->_hostname;
	}

	public function setHttpsPort($value)
	{
		/* if ( $value === null ) {
			$this->_httpsPort = 'NULL';
		} else */ $this->_httpsPort = $value;
		return $this;
	}

	public function getHttpsPort()
	{
		return $this->_httpsPort;
	}

	public function setVomsesPort($value)
	{
		/* if ( $value === null ) {
			$this->_vomsesPort = 'NULL';
		} else */ $this->_vomsesPort = $value;
		return $this;
	}

	public function getVomsesPort()
	{
		return $this->_vomsesPort;
	}

	public function setIsAdmin($value)
	{
		/* if ( $value === null ) {
			$this->_isAdmin = 'NULL';
		} else */ $this->_isAdmin = $value;
		return $this;
	}

	public function getIsAdmin()
	{
		$v = $this->_isAdmin;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setMemberListUrl($value)
	{
		/* if ( $value === null ) {
			$this->_memberListUrl = 'NULL';
		} else */ $this->_memberListUrl = $value;
		return $this;
	}

	public function getMemberListUrl()
	{
		return $this->_memberListUrl;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_VOMSesMapper());
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
		$XML = "<VOMS>\n";
		if ($this->_voID !== null) $XML .= "<voID>".$this->_voID."</voID>\n";
		if ( $recursive ) if ( $this->_VO === null ) $this->getVO();
		if ( ! ($this->_VO === null) ) $XML .= $this->_VO->toXML();
		if ($this->_hostname !== null) $XML .= "<hostname>".recode_string("utf8..xml",$this->_hostname)."</hostname>\n";
		if ($this->_httpsPort !== null) $XML .= "<httpsPort>".$this->_httpsPort."</httpsPort>\n";
		if ($this->_vomsesPort !== null) $XML .= "<vomsesPort>".$this->_vomsesPort."</vomsesPort>\n";
		if ($this->_isAdmin !== null) $XML .= "<isAdmin>".$this->_isAdmin."</isAdmin>\n";
		if ($this->_memberListUrl !== null) $XML .= "<memberListUrl>".recode_string("utf8..xml",$this->_memberListUrl)."</memberListUrl>\n";
		$XML .= "</VOMS>\n";
		return $XML;
	}
}

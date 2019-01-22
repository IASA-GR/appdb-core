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
class Default_Model_LinkStatusBase
{
	protected $_mapper;
	protected $_appName;
	protected $_ownerID;
	protected $_owner;
	protected $_ownerName;
	protected $_contact;
	protected $_title;
	protected $_linkID;
	protected $_appID;
	protected $_application;
	protected $_linkType;
	protected $_urlName;
	protected $_parentName;
	protected $_baseRef;
	protected $_valID;
	protected $_result;
	protected $_warning;
	protected $_info;
	protected $_url;
	protected $_line;
	protected $_col;
	protected $_name;
	protected $_checkTime;
	protected $_dlTime;
	protected $_dlSize;
	protected $_cached;
	protected $_firstChecked;
	protected $_lastChecked;
	protected $_age;
	protected $_whitelisted;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid LinkStatus property: '$name'");
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
			throw new Exception("Invalid LinkStatus property: '$name'");
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

	public function setAppName($value)
	{
		/* if ( $value === null ) {
			$this->_appName = 'NULL';
		} else */ $this->_appName = $value;
		return $this;
	}

	public function getAppName()
	{
		return $this->_appName;
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


	public function setOwnerName($value)
	{
		/* if ( $value === null ) {
			$this->_ownerName = 'NULL';
		} else */ $this->_ownerName = $value;
		return $this;
	}

	public function getOwnerName()
	{
		return $this->_ownerName;
	}

	public function setContact($value)
	{
		/* if ( $value === null ) {
			$this->_contact = 'NULL';
		} else */ $this->_contact = $value;
		return $this;
	}

	public function getContact()
	{
		return $this->_contact;
	}

	public function setTitle($value)
	{
		/* if ( $value === null ) {
			$this->_title = 'NULL';
		} else */ $this->_title = $value;
		return $this;
	}

	public function getTitle()
	{
		return $this->_title;
	}

	public function setLinkID($value)
	{
		/* if ( $value === null ) {
			$this->_linkID = 'NULL';
		} else */ $this->_linkID = $value;
		return $this;
	}

	public function getLinkID()
	{
		return $this->_linkID;
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


	public function setLinkType($value)
	{
		/* if ( $value === null ) {
			$this->_linkType = 'NULL';
		} else */ $this->_linkType = $value;
		return $this;
	}

	public function getLinkType()
	{
		return $this->_linkType;
	}

	public function setUrlName($value)
	{
		/* if ( $value === null ) {
			$this->_urlName = 'NULL';
		} else */ $this->_urlName = $value;
		return $this;
	}

	public function getUrlName()
	{
		return $this->_urlName;
	}

	public function setParentName($value)
	{
		/* if ( $value === null ) {
			$this->_parentName = 'NULL';
		} else */ $this->_parentName = $value;
		return $this;
	}

	public function getParentName()
	{
		return $this->_parentName;
	}

	public function setBaseRef($value)
	{
		/* if ( $value === null ) {
			$this->_baseRef = 'NULL';
		} else */ $this->_baseRef = $value;
		return $this;
	}

	public function getBaseRef()
	{
		return $this->_baseRef;
	}

	public function setValID($value)
	{
		/* if ( $value === null ) {
			$this->_valID = 'NULL';
		} else */ $this->_valID = $value;
		return $this;
	}

	public function getValID()
	{
		return $this->_valID;
	}

	public function setResult($value)
	{
		/* if ( $value === null ) {
			$this->_result = 'NULL';
		} else */ $this->_result = $value;
		return $this;
	}

	public function getResult()
	{
		return $this->_result;
	}

	public function setWarning($value)
	{
		/* if ( $value === null ) {
			$this->_warning = 'NULL';
		} else */ $this->_warning = $value;
		return $this;
	}

	public function getWarning()
	{
		return $this->_warning;
	}

	public function setInfo($value)
	{
		/* if ( $value === null ) {
			$this->_info = 'NULL';
		} else */ $this->_info = $value;
		return $this;
	}

	public function getInfo()
	{
		return $this->_info;
	}

	public function setUrl($value)
	{
		/* if ( $value === null ) {
			$this->_url = 'NULL';
		} else */ $this->_url = $value;
		return $this;
	}

	public function getUrl()
	{
		return $this->_url;
	}

	public function setLine($value)
	{
		/* if ( $value === null ) {
			$this->_line = 'NULL';
		} else */ $this->_line = $value;
		return $this;
	}

	public function getLine()
	{
		return $this->_line;
	}

	public function setCol($value)
	{
		/* if ( $value === null ) {
			$this->_col = 'NULL';
		} else */ $this->_col = $value;
		return $this;
	}

	public function getCol()
	{
		return $this->_col;
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

	public function setCheckTime($value)
	{
		/* if ( $value === null ) {
			$this->_checkTime = 'NULL';
		} else */ $this->_checkTime = $value;
		return $this;
	}

	public function getCheckTime()
	{
		return $this->_checkTime;
	}

	public function setDlTime($value)
	{
		/* if ( $value === null ) {
			$this->_dlTime = 'NULL';
		} else */ $this->_dlTime = $value;
		return $this;
	}

	public function getDlTime()
	{
		return $this->_dlTime;
	}

	public function setDlSize($value)
	{
		/* if ( $value === null ) {
			$this->_dlSize = 'NULL';
		} else */ $this->_dlSize = $value;
		return $this;
	}

	public function getDlSize()
	{
		return $this->_dlSize;
	}

	public function setCached($value)
	{
		/* if ( $value === null ) {
			$this->_cached = 'NULL';
		} else */ $this->_cached = $value;
		return $this;
	}

	public function getCached()
	{
		return $this->_cached;
	}

	public function setFirstChecked($value)
	{
		/* if ( $value === null ) {
			$this->_firstChecked = 'NULL';
		} else */ $this->_firstChecked = $value;
		return $this;
	}

	public function getFirstChecked()
	{
		return $this->_firstChecked;
	}

	public function setLastChecked($value)
	{
		/* if ( $value === null ) {
			$this->_lastChecked = 'NULL';
		} else */ $this->_lastChecked = $value;
		return $this;
	}

	public function getLastChecked()
	{
		return $this->_lastChecked;
	}

	public function setAge($value)
	{
		/* if ( $value === null ) {
			$this->_age = 'NULL';
		} else */ $this->_age = $value;
		return $this;
	}

	public function getAge()
	{
		return $this->_age;
	}

	public function setWhitelisted($value)
	{
		/* if ( $value === null ) {
			$this->_whitelisted = 'NULL';
		} else */ $this->_whitelisted = $value;
		return $this;
	}

	public function getWhitelisted()
	{
		$v = $this->_whitelisted;
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
			$this->setMapper(new Default_Model_LinkStatusesMapper());
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
		$XML = "<LinkStatus>\n";
		if ($this->_appName !== null) $XML .= "<appName>".recode_string("utf8..xml",$this->_appName)."</appName>\n";
		if ($this->_ownerID !== null) $XML .= "<ownerID>".$this->_ownerID."</ownerID>\n";
		if ( $recursive ) if ( $this->_owner === null ) $this->getOwner();
		if ( ! ($this->_owner === null) ) $XML .= $this->_owner->toXML();
		if ($this->_ownerName !== null) $XML .= "<ownerName>".recode_string("utf8..xml",$this->_ownerName)."</ownerName>\n";
		if ($this->_contact !== null) $XML .= "<contact>".recode_string("utf8..xml",$this->_contact)."</contact>\n";
		if ($this->_title !== null) $XML .= "<title>".recode_string("utf8..xml",$this->_title)."</title>\n";
		if ($this->_linkID !== null) $XML .= "<linkID>".recode_string("utf8..xml",$this->_linkID)."</linkID>\n";
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ( $recursive ) if ( $this->_application === null ) $this->getApplication();
		if ( ! ($this->_application === null) ) $XML .= $this->_application->toXML();
		if ($this->_linkType !== null) $XML .= "<linkType>".recode_string("utf8..xml",$this->_linkType)."</linkType>\n";
		if ($this->_urlName !== null) $XML .= "<urlName>".recode_string("utf8..xml",$this->_urlName)."</urlName>\n";
		if ($this->_parentName !== null) $XML .= "<parentName>".recode_string("utf8..xml",$this->_parentName)."</parentName>\n";
		if ($this->_baseRef !== null) $XML .= "<baseRef>".recode_string("utf8..xml",$this->_baseRef)."</baseRef>\n";
		if ($this->_valID !== null) $XML .= "<valID>".$this->_valID."</valID>\n";
		if ($this->_result !== null) $XML .= "<result>".recode_string("utf8..xml",$this->_result)."</result>\n";
		if ($this->_warning !== null) $XML .= "<warning>".recode_string("utf8..xml",$this->_warning)."</warning>\n";
		if ($this->_info !== null) $XML .= "<info>".recode_string("utf8..xml",$this->_info)."</info>\n";
		if ($this->_url !== null) $XML .= "<url>".recode_string("utf8..xml",$this->_url)."</url>\n";
		if ($this->_line !== null) $XML .= "<line>".$this->_line."</line>\n";
		if ($this->_col !== null) $XML .= "<col>".$this->_col."</col>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_checkTime !== null) $XML .= "<checkTime>".$this->_checkTime."</checkTime>\n";
		if ($this->_dlTime !== null) $XML .= "<dlTime>".$this->_dlTime."</dlTime>\n";
		if ($this->_dlSize !== null) $XML .= "<dlSize>".$this->_dlSize."</dlSize>\n";
		if ($this->_cached !== null) $XML .= "<cached>".$this->_cached."</cached>\n";
		if ($this->_firstChecked !== null) $XML .= "<firstChecked>".recode_string("utf8..xml",$this->_firstChecked)."</firstChecked>\n";
		if ($this->_lastChecked !== null) $XML .= "<lastChecked>".recode_string("utf8..xml",$this->_lastChecked)."</lastChecked>\n";
		if ($this->_age !== null) $XML .= "<age>".recode_string("utf8..xml",$this->_age)."</age>\n";
		if ($this->_whitelisted !== null) $XML .= "<whitelisted>".$this->_whitelisted."</whitelisted>\n";
		$XML .= "</LinkStatus>\n";
		return $XML;
	}
}

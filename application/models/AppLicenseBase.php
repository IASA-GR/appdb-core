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
class Default_Model_AppLicenseBase
{
	protected $_mapper;
	protected $_id;
	protected $_appID;
	protected $_application;
	protected $_licenseID;
	protected $_license;
	protected $_title;
	protected $_comment;
	protected $_link;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AppLicense property: '$name'");
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
			throw new Exception("Invalid AppLicense property: '$name'");
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


	public function setLicenseID($value)
	{
		/* if ( $value === null ) {
			$this->_licenseID = 'NULL';
		} else */ $this->_licenseID = $value;
		return $this;
	}

	public function getLicenseID()
	{
		return $this->_licenseID;
	}

	public function getLicense()
	{
		if ( $this->_license === null ) {
			$Licenses = new Default_Model_Licenses();
			$Licenses->filter->id->equals($this->getLicenseID());
			if ($Licenses->count() > 0) $this->_license = $Licenses->items[0];
		}
		return $this->_license;
	}

	public function setLicense($value)
	{
		if ( $value === null ) {
			$this->setLicenseID(null);
		} else {
			$this->setLicenseID($value->getId());
		}
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

	public function setComment($value)
	{
		/* if ( $value === null ) {
			$this->_comment = 'NULL';
		} else */ $this->_comment = $value;
		return $this;
	}

	public function getComment()
	{
		return $this->_comment;
	}

	public function setLink($value)
	{
		/* if ( $value === null ) {
			$this->_link = 'NULL';
		} else */ $this->_link = $value;
		return $this;
	}

	public function getLink()
	{
		return $this->_link;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_AppLicensesMapper());
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
		$XML = "<AppLicense>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ( $recursive ) if ( $this->_application === null ) $this->getApplication();
		if ( ! ($this->_application === null) ) $XML .= $this->_application->toXML();
		if ($this->_licenseID !== null) $XML .= "<licenseID>".$this->_licenseID."</licenseID>\n";
		if ( $recursive ) if ( $this->_license === null ) $this->getLicense();
		if ( ! ($this->_license === null) ) $XML .= $this->_license->toXML();
		if ($this->_title !== null) $XML .= "<title>".recode_string("utf8..xml",$this->_title)."</title>\n";
		if ($this->_comment !== null) $XML .= "<comment>".recode_string("utf8..xml",$this->_comment)."</comment>\n";
		if ($this->_link !== null) $XML .= "<link>".recode_string("utf8..xml",$this->_link)."</link>\n";
		$XML .= "</AppLicense>\n";
		return $XML;
	}
}

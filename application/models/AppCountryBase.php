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
class Default_Model_AppCountryBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_continent;
	protected $_ISOcode;
	protected $_regionID;
	protected $_region;
	protected $_appID;
	protected $_application;
	protected $_inherited;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AppCountry property: '$name'");
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
			throw new Exception("Invalid AppCountry property: '$name'");
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

	public function setContinent($value)
	{
		/* if ( $value === null ) {
			$this->_continent = 'NULL';
		} else */ $this->_continent = $value;
		return $this;
	}

	public function getContinent()
	{
		$v = $this->_continent;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setISOcode($value)
	{
		/* if ( $value === null ) {
			$this->_ISOcode = 'NULL';
		} else */ $this->_ISOcode = $value;
		return $this;
	}

	public function getISOcode()
	{
		return $this->_ISOcode;
	}

	public function setRegionID($value)
	{
		/* if ( $value === null ) {
			$this->_regionID = 'NULL';
		} else */ $this->_regionID = $value;
		return $this;
	}

	public function getRegionID()
	{
		return $this->_regionID;
	}

	public function getRegion()
	{
		if ( $this->_region === null ) {
			$Regions = new Default_Model_Regions();
			$Regions->filter->id->equals($this->getRegionID());
			if ($Regions->count() > 0) $this->_region = $Regions->items[0];
		}
		return $this->_region;
	}

	public function setRegion($value)
	{
		if ( $value === null ) {
			$this->setRegionID(null);
		} else {
			$this->setRegionID($value->getId());
		}
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


	public function setInherited($value)
	{
		/* if ( $value === null ) {
			$this->_inherited = 'NULL';
		} else */ $this->_inherited = $value;
		return $this;
	}

	public function getInherited()
	{
		$v = $this->_inherited;
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
			$this->setMapper(new Default_Model_AppCountriesMapper());
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
		$XML = "<AppCountry>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_continent !== null) $XML .= "<continent>".$this->_continent."</continent>\n";
		if ($this->_ISOcode !== null) $XML .= "<ISOcode>".recode_string("utf8..xml",$this->_ISOcode)."</ISOcode>\n";
		if ($this->_regionID !== null) $XML .= "<regionID>".$this->_regionID."</regionID>\n";
		if ( $recursive ) if ( $this->_region === null ) $this->getRegion();
		if ( ! ($this->_region === null) ) $XML .= $this->_region->toXML();
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ( $recursive ) if ( $this->_application === null ) $this->getApplication();
		if ( ! ($this->_application === null) ) $XML .= $this->_application->toXML();
		if ($this->_inherited !== null) $XML .= "<inherited>".$this->_inherited."</inherited>\n";
		$XML .= "</AppCountry>\n";
		return $XML;
	}
}

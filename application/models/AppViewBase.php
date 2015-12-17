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
class Default_Model_AppViewBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_description;
	protected $_abstract;
	protected $_logo;
	protected $_statusID;
	protected $_status;
	protected $_middlewareID;
	protected $_middleware;
	protected $_dateAdded;
	protected $_addedBy;
	protected $_researcher;
	protected $_tool;
	protected $_respect;
	protected $_countryID;
	protected $_country;
	protected $_regionID;
	protected $_region;
	protected $_voID;
	protected $_vo;
	protected $_personData;
	protected $_hasDocs;
	protected $_guid;
	protected $_deleted;
	protected $_moderated;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AppView property: '$name'");
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
			throw new Exception("Invalid AppView property: '$name'");
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

	public function setDescription($value)
	{
		/* if ( $value === null ) {
			$this->_description = 'NULL';
		} else */ $this->_description = $value;
		return $this;
	}

	public function getDescription()
	{
		return $this->_description;
	}

	public function setAbstract($value)
	{
		/* if ( $value === null ) {
			$this->_abstract = 'NULL';
		} else */ $this->_abstract = $value;
		return $this;
	}

	public function getAbstract()
	{
		return $this->_abstract;
	}

	public function setLogo($value)
	{
		/* if ( $value === null ) {
			$this->_logo = 'NULL';
		} else */ $this->_logo = $value;
		return $this;
	}

	public function getLogo()
	{
		return $this->_logo;
	}

	public function setStatusID($value)
	{
		/* if ( $value === null ) {
			$this->_statusID = 'NULL';
		} else */ $this->_statusID = $value;
		return $this;
	}

	public function getStatusID()
	{
		return $this->_statusID;
	}

	public function getStatus()
	{
		if ( $this->_status === null ) {
			$Statuses = new Default_Model_Statuses();
			$Statuses->filter->id->equals($this->getStatusID());
			if ($Statuses->count() > 0) $this->_status = $Statuses->items[0];
		}
		return $this->_status;
	}

	public function setStatus($value)
	{
		if ( $value === null ) {
			$this->setStatusID(null);
		} else {
			$this->setStatusID($value->getId());
		}
	}


	public function setMiddlewareID($value)
	{
		/* if ( $value === null ) {
			$this->_middlewareID = 'NULL';
		} else */ $this->_middlewareID = $value;
		return $this;
	}

	public function getMiddlewareID()
	{
		return $this->_middlewareID;
	}

	public function getMiddleware()
	{
		if ( $this->_middleware === null ) {
			$Middlewares = new Default_Model_Middlewares();
			$Middlewares->filter->id->equals($this->getMiddlewareID());
			if ($Middlewares->count() > 0) $this->_middleware = $Middlewares->items[0];
		}
		return $this->_middleware;
	}

	public function setMiddleware($value)
	{
		if ( $value === null ) {
			$this->setMiddlewareID(null);
		} else {
			$this->setMiddlewareID($value->getId());
		}
	}


	public function setDateAdded($value)
	{
		/* if ( $value === null ) {
			$this->_dateAdded = 'NULL';
		} else */ $this->_dateAdded = $value;
		return $this;
	}

	public function getDateAdded()
	{
		return $this->_dateAdded;
	}

	public function setAddedBy($value)
	{
		/* if ( $value === null ) {
			$this->_addedBy = 'NULL';
		} else */ $this->_addedBy = $value;
		return $this;
	}

	public function getAddedBy()
	{
		return $this->_addedBy;
	}

	public function getResearcher()
	{
		if ( $this->_researcher === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getAddedBy());
			if ($Researchers->count() > 0) $this->_researcher = $Researchers->items[0];
		}
		return $this->_researcher;
	}

	public function setResearcher($value)
	{
		if ( $value === null ) {
			$this->setAddedBy(null);
		} else {
			$this->setAddedBy($value->getId());
		}
	}


	public function setTool($value)
	{
		/* if ( $value === null ) {
			$this->_tool = 'NULL';
		} else */ $this->_tool = $value;
		return $this;
	}

	public function getTool()
	{
		$v = $this->_tool;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setRespect($value)
	{
		/* if ( $value === null ) {
			$this->_respect = 'NULL';
		} else */ $this->_respect = $value;
		return $this;
	}

	public function getRespect()
	{
		$v = $this->_respect;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setCountryID($value)
	{
		/* if ( $value === null ) {
			$this->_countryID = 'NULL';
		} else */ $this->_countryID = $value;
		return $this;
	}

	public function getCountryID()
	{
		return $this->_countryID;
	}

	public function getCountry()
	{
		if ( $this->_country === null ) {
			$Countries = new Default_Model_Countries();
			$Countries->filter->id->equals($this->getCountryID());
			if ($Countries->count() > 0) $this->_country = $Countries->items[0];
		}
		return $this->_country;
	}

	public function setCountry($value)
	{
		if ( $value === null ) {
			$this->setCountryID(null);
		} else {
			$this->setCountryID($value->getId());
		}
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

	public function getVo()
	{
		if ( $this->_vo === null ) {
			$VOs = new Default_Model_VOs();
			$VOs->filter->id->equals($this->getVoID());
			if ($VOs->count() > 0) $this->_vo = $VOs->items[0];
		}
		return $this->_vo;
	}

	public function setVo($value)
	{
		if ( $value === null ) {
			$this->setVoID(null);
		} else {
			$this->setVoID($value->getId());
		}
	}


	public function setPersonData($value)
	{
		/* if ( $value === null ) {
			$this->_personData = 'NULL';
		} else */ $this->_personData = $value;
		return $this;
	}

	public function getPersonData()
	{
		return $this->_personData;
	}

	public function setHasDocs($value)
	{
		/* if ( $value === null ) {
			$this->_hasDocs = 'NULL';
		} else */ $this->_hasDocs = $value;
		return $this;
	}

	public function getHasDocs()
	{
		$v = $this->_hasDocs;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setGuid($value)
	{
		/* if ( $value === null ) {
			$this->_guid = 'NULL';
		} else */ $this->_guid = $value;
		return $this;
	}

	public function getGuid()
	{
		return $this->_guid;
	}

	public function setDeleted($value)
	{
		/* if ( $value === null ) {
			$this->_deleted = 'NULL';
		} else */ $this->_deleted = $value;
		return $this;
	}

	public function getDeleted()
	{
		$v = $this->_deleted;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setModerated($value)
	{
		/* if ( $value === null ) {
			$this->_moderated = 'NULL';
		} else */ $this->_moderated = $value;
		return $this;
	}

	public function getModerated()
	{
		$v = $this->_moderated;
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
			$this->setMapper(new Default_Model_AppViewsMapper());
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
		$XML = "<AppView>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_abstract !== null) $XML .= "<abstract>".recode_string("utf8..xml",$this->_abstract)."</abstract>\n";
		if ($this->_logo !== null) $XML .= "<logo>".recode_string("utf8..xml",$this->_logo)."</logo>\n";
		if ($this->_statusID !== null) $XML .= "<statusID>".$this->_statusID."</statusID>\n";
		if ( $recursive ) if ( $this->_status === null ) $this->getStatus();
		if ( ! ($this->_status === null) ) $XML .= $this->_status->toXML();
		if ($this->_middlewareID !== null) $XML .= "<middlewareID>".$this->_middlewareID."</middlewareID>\n";
		if ( $recursive ) if ( $this->_middleware === null ) $this->getMiddleware();
		if ( ! ($this->_middleware === null) ) $XML .= $this->_middleware->toXML();
		if ($this->_dateAdded !== null) $XML .= "<dateAdded>".recode_string("utf8..xml",$this->_dateAdded)."</dateAdded>\n";
		if ($this->_addedBy !== null) $XML .= "<addedBy>".$this->_addedBy."</addedBy>\n";
		if ( $recursive ) if ( $this->_researcher === null ) $this->getResearcher();
		if ( ! ($this->_researcher === null) ) $XML .= $this->_researcher->toXML();
		if ($this->_tool !== null) $XML .= "<tool>".$this->_tool."</tool>\n";
		if ($this->_respect !== null) $XML .= "<respect>".$this->_respect."</respect>\n";
		if ($this->_countryID !== null) $XML .= "<countryID>".$this->_countryID."</countryID>\n";
		if ( $recursive ) if ( $this->_country === null ) $this->getCountry();
		if ( ! ($this->_country === null) ) $XML .= $this->_country->toXML();
		if ($this->_regionID !== null) $XML .= "<regionID>".$this->_regionID."</regionID>\n";
		if ( $recursive ) if ( $this->_region === null ) $this->getRegion();
		if ( ! ($this->_region === null) ) $XML .= $this->_region->toXML();
		if ($this->_voID !== null) $XML .= "<voID>".$this->_voID."</voID>\n";
		if ( $recursive ) if ( $this->_vo === null ) $this->getVo();
		if ( ! ($this->_vo === null) ) $XML .= $this->_vo->toXML();
		if ($this->_personData !== null) $XML .= "<personData>".recode_string("utf8..xml",$this->_personData)."</personData>\n";
		if ($this->_hasDocs !== null) $XML .= "<hasDocs>".$this->_hasDocs."</hasDocs>\n";
		if ($this->_guid !== null) $XML .= "<guid>".recode_string("utf8..xml",$this->_guid)."</guid>\n";
		if ($this->_deleted !== null) $XML .= "<deleted>".$this->_deleted."</deleted>\n";
		if ($this->_moderated !== null) $XML .= "<moderated>".$this->_moderated."</moderated>\n";
		$XML .= "</AppView>\n";
		return $XML;
	}
}

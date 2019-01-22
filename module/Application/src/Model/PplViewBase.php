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
class Default_Model_PplViewBase
{
	protected $_mapper;
	protected $_id;
	protected $_firstName;
	protected $_lastName;
	protected $_dateInclusion;
	protected $_institution;
	protected $_countryID;
	protected $_country;
	protected $_positionTypeID;
	protected $_positionType;
	protected $_image;
	protected $_name;
	protected $_regionID;
	protected $_region;
	protected $_docCount;
	protected $_hasDocs;
	protected $_guid;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid PplView property: '$name'");
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
			throw new Exception("Invalid PplView property: '$name'");
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

	public function setFirstName($value)
	{
		/* if ( $value === null ) {
			$this->_firstName = 'NULL';
		} else */ $this->_firstName = $value;
		return $this;
	}

	public function getFirstName()
	{
		return $this->_firstName;
	}

	public function setLastName($value)
	{
		/* if ( $value === null ) {
			$this->_lastName = 'NULL';
		} else */ $this->_lastName = $value;
		return $this;
	}

	public function getLastName()
	{
		return $this->_lastName;
	}

	public function setDateInclusion($value)
	{
		/* if ( $value === null ) {
			$this->_dateInclusion = 'NULL';
		} else */ $this->_dateInclusion = $value;
		return $this;
	}

	public function getDateInclusion()
	{
		return $this->_dateInclusion;
	}

	public function setInstitution($value)
	{
		/* if ( $value === null ) {
			$this->_institution = 'NULL';
		} else */ $this->_institution = $value;
		return $this;
	}

	public function getInstitution()
	{
		return $this->_institution;
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


	public function setPositionTypeID($value)
	{
		/* if ( $value === null ) {
			$this->_positionTypeID = 'NULL';
		} else */ $this->_positionTypeID = $value;
		return $this;
	}

	public function getPositionTypeID()
	{
		return $this->_positionTypeID;
	}

	public function getPositionType()
	{
		if ( $this->_positionType === null ) {
			$PositionTypes = new Default_Model_PositionTypes();
			$PositionTypes->filter->id->equals($this->getPositionTypeID());
			if ($PositionTypes->count() > 0) $this->_positionType = $PositionTypes->items[0];
		}
		return $this->_positionType;
	}

	public function setPositionType($value)
	{
		if ( $value === null ) {
			$this->setPositionTypeID(null);
		} else {
			$this->setPositionTypeID($value->getId());
		}
	}


	public function setImage($value)
	{
		/* if ( $value === null ) {
			$this->_image = 'NULL';
		} else */ $this->_image = $value;
		return $this;
	}

	public function getImage()
	{
		return $this->_image;
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


	public function setDocCount($value)
	{
		/* if ( $value === null ) {
			$this->_docCount = 'NULL';
		} else */ $this->_docCount = $value;
		return $this;
	}

	public function getDocCount()
	{
		return $this->_docCount;
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

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_PplViewsMapper());
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
		$XML = "<PplView>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_firstName !== null) $XML .= "<firstName>".recode_string("utf8..xml",$this->_firstName)."</firstName>\n";
		if ($this->_lastName !== null) $XML .= "<lastName>".recode_string("utf8..xml",$this->_lastName)."</lastName>\n";
		if ($this->_dateInclusion !== null) $XML .= "<dateInclusion>".recode_string("utf8..xml",$this->_dateInclusion)."</dateInclusion>\n";
		if ($this->_institution !== null) $XML .= "<institution>".recode_string("utf8..xml",$this->_institution)."</institution>\n";
		if ($this->_countryID !== null) $XML .= "<countryID>".$this->_countryID."</countryID>\n";
		if ( $recursive ) if ( $this->_country === null ) $this->getCountry();
		if ( ! ($this->_country === null) ) $XML .= $this->_country->toXML();
		if ($this->_positionTypeID !== null) $XML .= "<positionTypeID>".$this->_positionTypeID."</positionTypeID>\n";
		if ( $recursive ) if ( $this->_positionType === null ) $this->getPositionType();
		if ( ! ($this->_positionType === null) ) $XML .= $this->_positionType->toXML();
		if ($this->_image !== null) $XML .= "<image>".recode_string("utf8..xml",$this->_image)."</image>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_regionID !== null) $XML .= "<regionID>".$this->_regionID."</regionID>\n";
		if ( $recursive ) if ( $this->_region === null ) $this->getRegion();
		if ( ! ($this->_region === null) ) $XML .= $this->_region->toXML();
		if ($this->_docCount !== null) $XML .= "<docCount>".recode_string("utf8..xml",$this->_docCount)."</docCount>\n";
		if ($this->_hasDocs !== null) $XML .= "<hasDocs>".$this->_hasDocs."</hasDocs>\n";
		if ($this->_guid !== null) $XML .= "<guid>".recode_string("utf8..xml",$this->_guid)."</guid>\n";
		$XML .= "</PplView>\n";
		return $XML;
	}
}

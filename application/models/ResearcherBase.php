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
class Default_Model_ResearcherBase
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
	protected $_guid;
	protected $_gender;
	protected $_lastUpdated;
	protected $_name;
	protected $_mailUnsubscribePwd;
	protected $_lastLogin;
	protected $_noDissemination;
	protected $_accountType;
	protected $_deleted;
	protected $_hitcount;
	protected $_cname;
	protected $_addedByID;
	protected $_addedBy;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Researcher property: '$name'");
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
			throw new Exception("Invalid Researcher property: '$name'");
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

	public function setGender($value)
	{
		/* if ( $value === null ) {
			$this->_gender = 'NULL';
		} else */ $this->_gender = $value;
		return $this;
	}

	public function getGender()
	{
		return $this->_gender;
	}

	public function setLastUpdated($value)
	{
		/* if ( $value === null ) {
			$this->_lastUpdated = 'NULL';
		} else */ $this->_lastUpdated = $value;
		return $this;
	}

	public function getLastUpdated()
	{
		return $this->_lastUpdated;
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

	public function setMailUnsubscribePwd($value)
	{
		/* if ( $value === null ) {
			$this->_mailUnsubscribePwd = 'NULL';
		} else */ $this->_mailUnsubscribePwd = $value;
		return $this;
	}

	public function getMailUnsubscribePwd()
	{
		return $this->_mailUnsubscribePwd;
	}

	public function setLastLogin($value)
	{
		/* if ( $value === null ) {
			$this->_lastLogin = 'NULL';
		} else */ $this->_lastLogin = $value;
		return $this;
	}

	public function getLastLogin()
	{
		return $this->_lastLogin;
	}

	public function setNoDissemination($value)
	{
		/* if ( $value === null ) {
			$this->_noDissemination = 'NULL';
		} else */ $this->_noDissemination = $value;
		return $this;
	}

	public function getNoDissemination()
	{
		$v = $this->_noDissemination;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
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

	public function setHitcount($value)
	{
		/* if ( $value === null ) {
			$this->_hitcount = 'NULL';
		} else */ $this->_hitcount = $value;
		return $this;
	}

	public function getHitcount()
	{
		return $this->_hitcount;
	}

	public function setCname($value)
	{
		/* if ( $value === null ) {
			$this->_cname = 'NULL';
		} else */ $this->_cname = $value;
		return $this;
	}

	public function getCname()
	{
		return $this->_cname;
	}

	public function setAddedByID($value)
	{
		/* if ( $value === null ) {
			$this->_addedByID = 'NULL';
		} else */ $this->_addedByID = $value;
		return $this;
	}

	public function getAddedByID()
	{
		return $this->_addedByID;
	}

	public function getAddedBy()
	{
		if ( $this->_addedBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getAddedByID());
			if ($Researchers->count() > 0) $this->_addedBy = $Researchers->items[0];
		}
		return $this->_addedBy;
	}

	public function setAddedBy($value)
	{
		if ( $value === null ) {
			$this->setAddedByID(null);
		} else {
			$this->setAddedByID($value->getId());
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
			$this->setMapper(new Default_Model_ResearchersMapper());
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
		$XML = "<Researcher>\n";
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
		if ($this->_guid !== null) $XML .= "<guid>".recode_string("utf8..xml",$this->_guid)."</guid>\n";
		if ($this->_gender !== null) $XML .= "<gender>".recode_string("utf8..xml",$this->_gender)."</gender>\n";
		if ($this->_lastUpdated !== null) $XML .= "<lastUpdated>".recode_string("utf8..xml",$this->_lastUpdated)."</lastUpdated>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_mailUnsubscribePwd !== null) $XML .= "<mailUnsubscribePwd>".recode_string("utf8..xml",$this->_mailUnsubscribePwd)."</mailUnsubscribePwd>\n";
		if ($this->_lastLogin !== null) $XML .= "<lastLogin>".recode_string("utf8..xml",$this->_lastLogin)."</lastLogin>\n";
		if ($this->_noDissemination !== null) $XML .= "<noDissemination>".$this->_noDissemination."</noDissemination>\n";
		if ($this->_accountType !== null) $XML .= "<accountType>".$this->_accountType."</accountType>\n";
		if ($this->_deleted !== null) $XML .= "<deleted>".$this->_deleted."</deleted>\n";
		if ($this->_hitcount !== null) $XML .= "<hitcount>".$this->_hitcount."</hitcount>\n";
		if ($this->_cname !== null) $XML .= "<cname>".recode_string("utf8..xml",$this->_cname)."</cname>\n";
		if ($this->_addedByID !== null) $XML .= "<addedByID>".$this->_addedByID."</addedByID>\n";
		if ( $recursive ) if ( $this->_addedBy === null ) $this->getAddedBy();
		if ( ! ($this->_addedBy === null) ) $XML .= $this->_addedBy->toXML();
		$XML .= "</Researcher>\n";
		return $XML;
	}
}

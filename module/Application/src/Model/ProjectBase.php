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
class Default_Model_ProjectBase
{
	protected $_mapper;
	protected $_id;
	protected $_code;
	protected $_acronym;
	protected $_title;
	protected $_startDate;
	protected $_endDate;
	protected $_callIdentifier;
	protected $_websiteURL;
	protected $_keywords;
	protected $_duration;
	protected $_contractTypeID;
	protected $_contractType;
	protected $_fundingID;
	protected $_funding;
	protected $_addedOn;
	protected $_addedByID;
	protected $_addedBy;
	protected $_guid;
	protected $_identifier;
	protected $_sourceID;
	protected $_entitysource;
	protected $_deletedon;
	protected $_deletedby;
	protected $_deletedBy;
	protected $_deletedByID;
	protected $_extIdentifier;
	protected $_moderated;
	protected $_deleted;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Project property: '$name'");
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
			throw new Exception("Invalid Project property: '$name'");
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

	public function setAcronym($value)
	{
		/* if ( $value === null ) {
			$this->_acronym = 'NULL';
		} else */ $this->_acronym = $value;
		return $this;
	}

	public function getAcronym()
	{
		return $this->_acronym;
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

	public function setStartDate($value)
	{
		/* if ( $value === null ) {
			$this->_startDate = 'NULL';
		} else */ $this->_startDate = $value;
		return $this;
	}

	public function getStartDate()
	{
		return $this->_startDate;
	}

	public function setEndDate($value)
	{
		/* if ( $value === null ) {
			$this->_endDate = 'NULL';
		} else */ $this->_endDate = $value;
		return $this;
	}

	public function getEndDate()
	{
		return $this->_endDate;
	}

	public function setCallIdentifier($value)
	{
		/* if ( $value === null ) {
			$this->_callIdentifier = 'NULL';
		} else */ $this->_callIdentifier = $value;
		return $this;
	}

	public function getCallIdentifier()
	{
		return $this->_callIdentifier;
	}

	public function setWebsiteURL($value)
	{
		/* if ( $value === null ) {
			$this->_websiteURL = 'NULL';
		} else */ $this->_websiteURL = $value;
		return $this;
	}

	public function getWebsiteURL()
	{
		return $this->_websiteURL;
	}

	public function setKeywords($value)
	{
		/* if ( $value === null ) {
			$this->_keywords = 'NULL';
		} else */ $this->_keywords = $value;
		return $this;
	}

	public function getKeywords()
	{
		return $this->_keywords;
	}

	public function setDuration($value)
	{
		/* if ( $value === null ) {
			$this->_duration = 'NULL';
		} else */ $this->_duration = $value;
		return $this;
	}

	public function getDuration()
	{
		return $this->_duration;
	}

	public function setContractTypeID($value)
	{
		/* if ( $value === null ) {
			$this->_contractTypeID = 'NULL';
		} else */ $this->_contractTypeID = $value;
		return $this;
	}

	public function getContractTypeID()
	{
		return $this->_contractTypeID;
	}

	public function getContractType()
	{
		if ( $this->_contractType === null ) {
			$ContractTypes = new Default_Model_ContractTypes();
			$ContractTypes->filter->id->equals($this->getContractTypeID());
			if ($ContractTypes->count() > 0) $this->_contractType = $ContractTypes->items[0];
		}
		return $this->_contractType;
	}

	public function setContractType($value)
	{
		if ( $value === null ) {
			$this->setContractTypeID(null);
		} else {
			$this->setContractTypeID($value->getId());
		}
	}


	public function setFundingID($value)
	{
		/* if ( $value === null ) {
			$this->_fundingID = 'NULL';
		} else */ $this->_fundingID = $value;
		return $this;
	}

	public function getFundingID()
	{
		return $this->_fundingID;
	}

	public function getFunding()
	{
		if ( $this->_funding === null ) {
			$Fundings = new Default_Model_Fundings();
			$Fundings->filter->id->equals($this->getFundingID());
			if ($Fundings->count() > 0) $this->_funding = $Fundings->items[0];
		}
		return $this->_funding;
	}

	public function setFunding($value)
	{
		if ( $value === null ) {
			$this->setFundingID(null);
		} else {
			$this->setFundingID($value->getId());
		}
	}


	public function setAddedOn($value)
	{
		/* if ( $value === null ) {
			$this->_addedOn = 'NULL';
		} else */ $this->_addedOn = $value;
		return $this;
	}

	public function getAddedOn()
	{
		return $this->_addedOn;
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

	public function setIdentifier($value)
	{
		/* if ( $value === null ) {
			$this->_identifier = 'NULL';
		} else */ $this->_identifier = $value;
		return $this;
	}

	public function getIdentifier()
	{
		return $this->_identifier;
	}

	public function setSourceID($value)
	{
		/* if ( $value === null ) {
			$this->_sourceID = 'NULL';
		} else */ $this->_sourceID = $value;
		return $this;
	}

	public function getSourceID()
	{
		return $this->_sourceID;
	}

	public function getEntitysource()
	{
		if ( $this->_entitysource === null ) {
			$Entitysources = new Default_Model_Entitysources();
			$Entitysources->filter->id->equals($this->getSourceID());
			if ($Entitysources->count() > 0) $this->_entitysource = $Entitysources->items[0];
		}
		return $this->_entitysource;
	}

	public function setEntitysource($value)
	{
		if ( $value === null ) {
			$this->setSourceID(null);
		} else {
			$this->setSourceID($value->getId());
		}
	}


	public function setDeletedon($value)
	{
		/* if ( $value === null ) {
			$this->_deletedon = 'NULL';
		} else */ $this->_deletedon = $value;
		return $this;
	}

	public function getDeletedon()
	{
		return $this->_deletedon;
	}

	public function setDeletedByID($value)
	{
		/* if ( $value === null ) {
			$this->_deletedby = 'NULL';
		} else */ $this->_deletedByID = $value;
		return $this;
	}

	public function getDeletedByID()
	{
		return $this->_deletedByID;
	}

	public function getDeletedBy()
	{
		if ( $this->_deletedBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getDeletedByID());
			if ($Researchers->count() > 0) $this->_deletedBy = $Researchers->items[0];
		}
		return $this->_deletedBy;
	}

	public function setDeletedBy($value)
	{
		if ( $value === null ) {
			$this->setDeletedByID(null);
		} else {
			$this->setDeletedByID($value->getId());
		}
	}


	public function setExtIdentifier($value)
	{
		/* if ( $value === null ) {
			$this->_extIdentifier = 'NULL';
		} else */ $this->_extIdentifier = $value;
		return $this;
	}

	public function getExtIdentifier()
	{
		return $this->_extIdentifier;
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

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_ProjectsMapper());
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
		$XML = "<Project>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_code !== null) $XML .= "<code>".recode_string("utf8..xml",$this->_code)."</code>\n";
		if ($this->_acronym !== null) $XML .= "<acronym>".recode_string("utf8..xml",$this->_acronym)."</acronym>\n";
		if ($this->_title !== null) $XML .= "<title>".recode_string("utf8..xml",$this->_title)."</title>\n";
		if ($this->_startDate !== null) $XML .= "<startDate>".recode_string("utf8..xml",$this->_startDate)."</startDate>\n";
		if ($this->_endDate !== null) $XML .= "<endDate>".recode_string("utf8..xml",$this->_endDate)."</endDate>\n";
		if ($this->_callIdentifier !== null) $XML .= "<callIdentifier>".recode_string("utf8..xml",$this->_callIdentifier)."</callIdentifier>\n";
		if ($this->_websiteURL !== null) $XML .= "<websiteURL>".recode_string("utf8..xml",$this->_websiteURL)."</websiteURL>\n";
		if ($this->_keywords !== null) $XML .= "<keywords>".recode_string("utf8..xml",$this->_keywords)."</keywords>\n";
		if ($this->_duration !== null) $XML .= "<duration>".recode_string("utf8..xml",$this->_duration)."</duration>\n";
		if ($this->_contractTypeID !== null) $XML .= "<contractTypeID>".$this->_contractTypeID."</contractTypeID>\n";
		if ( $recursive ) if ( $this->_contractType === null ) $this->getContractType();
		if ( ! ($this->_contractType === null) ) $XML .= $this->_contractType->toXML();
		if ($this->_fundingID !== null) $XML .= "<fundingID>".$this->_fundingID."</fundingID>\n";
		if ( $recursive ) if ( $this->_funding === null ) $this->getFunding();
		if ( ! ($this->_funding === null) ) $XML .= $this->_funding->toXML();
		if ($this->_addedOn !== null) $XML .= "<addedOn>".recode_string("utf8..xml",$this->_addedOn)."</addedOn>\n";
		if ($this->_addedByID !== null) $XML .= "<addedByID>".$this->_addedByID."</addedByID>\n";
		if ( $recursive ) if ( $this->_addedBy === null ) $this->getAddedBy();
		if ( ! ($this->_addedBy === null) ) $XML .= $this->_addedBy->toXML();
		if ($this->_guid !== null) $XML .= "<guid>".recode_string("utf8..xml",$this->_guid)."</guid>\n";
		if ($this->_identifier !== null) $XML .= "<identifier>".recode_string("utf8..xml",$this->_identifier)."</identifier>\n";
		if ($this->_sourceID !== null) $XML .= "<sourceID>".$this->_sourceID."</sourceID>\n";
		if ( $recursive ) if ( $this->_entitysource === null ) $this->getEntitysource();
		if ( ! ($this->_entitysource === null) ) $XML .= $this->_entitysource->toXML();
		if ($this->_deletedon !== null) $XML .= "<deletedon>".recode_string("utf8..xml",$this->_deletedon)."</deletedon>\n";
		if ($this->_deletedby !== null) $XML .= "<deletedby>".$this->_deletedby."</deletedby>\n";
		if ( $recursive ) if ( $this->_deletedBy === null ) $this->getDeletedBy();
		if ( ! ($this->_deletedBy === null) ) $XML .= $this->_deletedBy->toXML();
		if ($this->_extIdentifier !== null) $XML .= "<extIdentifier>".recode_string("utf8..xml",$this->_extIdentifier)."</extIdentifier>\n";
		if ($this->_moderated !== null) $XML .= "<moderated>".$this->_moderated."</moderated>\n";
		if ($this->_deleted !== null) $XML .= "<deleted>".$this->_deleted."</deleted>\n";
		$XML .= "</Project>\n";
		return $XML;
	}
}

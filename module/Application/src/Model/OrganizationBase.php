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
class Default_Model_OrganizationBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_shortName;
	protected $_websiteURL;
	protected $_countryID;
	protected $_country;
	protected $_addedOn;
	protected $_addedByID;
	protected $_addedBy;
	protected $_guid;
	protected $_identifier;
	protected $_sourceID;
	protected $_entitysource;
	protected $_deletedon;
	protected $_deletedByID;
	protected $_deletedBy;
	protected $_extIdentifier;
	protected $_moderated;
	protected $_deleted;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Organization property: '$name'");
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
			throw new Exception("Invalid Organization property: '$name'");
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

	public function setShortName($value)
	{
		/* if ( $value === null ) {
			$this->_shortName = 'NULL';
		} else */ $this->_shortName = $value;
		return $this;
	}

	public function getShortName()
	{
		return $this->_shortName;
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
			$this->_deletedByID = 'NULL';
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
			$this->setMapper(new Default_Model_OrganizationsMapper());
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
		$XML = "<Organization>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_shortName !== null) $XML .= "<shortName>".recode_string("utf8..xml",$this->_shortName)."</shortName>\n";
		if ($this->_websiteURL !== null) $XML .= "<websiteURL>".recode_string("utf8..xml",$this->_websiteURL)."</websiteURL>\n";
		if ($this->_countryID !== null) $XML .= "<countryID>".$this->_countryID."</countryID>\n";
		if ( $recursive ) if ( $this->_country === null ) $this->getCountry();
		if ( ! ($this->_country === null) ) $XML .= $this->_country->toXML();
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
		if ($this->_deletedByID !== null) $XML .= "<deletedByID>".$this->_deletedByID."</deletedByID>\n";
		if ( $recursive ) if ( $this->_deletedBy === null ) $this->getDeletedBy();
		if ( ! ($this->_deletedBy === null) ) $XML .= $this->_deletedBy->toXML();
		if ($this->_extIdentifier !== null) $XML .= "<extIdentifier>".recode_string("utf8..xml",$this->_extIdentifier)."</extIdentifier>\n";
		if ($this->_moderated !== null) $XML .= "<moderated>".$this->_moderated."</moderated>\n";
		if ($this->_deleted !== null) $XML .= "<deleted>".$this->_deleted."</deleted>\n";
		$XML .= "</Organization>\n";
		return $XML;
	}
}

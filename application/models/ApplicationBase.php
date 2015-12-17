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
class Default_Model_ApplicationBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_description;
	protected $_abstract;
	protected $_statusID;
	protected $_status;
	protected $_dateAdded;
	protected $_addedBy;
	protected $_researcher;
	protected $_respect;
	protected $_tool;
	protected $_guid;
	protected $_keywords;
	protected $_lastUpdated;
	protected $_rating;
	protected $_ratingCount;
	protected $_moderated;
	protected $_tagPolicy;
	protected $_deleted;
	protected $_metatype;
	protected $_disciplineID;
	protected $_ownerID;
	protected $_owner;
	protected $_categoryID;
	protected $_hitcount;
	protected $_cname;
	protected $_links;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Application property: '$name'");
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
			throw new Exception("Invalid Application property: '$name'");
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

	public function setRating($value)
	{
		/* if ( $value === null ) {
			$this->_rating = 'NULL';
		} else */ $this->_rating = $value;
		return $this;
	}

	public function getRating()
	{
		return $this->_rating;
	}

	public function setRatingCount($value)
	{
		/* if ( $value === null ) {
			$this->_ratingCount = 'NULL';
		} else */ $this->_ratingCount = $value;
		return $this;
	}

	public function getRatingCount()
	{
		return $this->_ratingCount;
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

	public function setTagPolicy($value)
	{
		/* if ( $value === null ) {
			$this->_tagPolicy = 'NULL';
		} else */ $this->_tagPolicy = $value;
		return $this;
	}

	public function getTagPolicy()
	{
		return $this->_tagPolicy;
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

	public function setMetatype($value)
	{
		/* if ( $value === null ) {
			$this->_metatype = 'NULL';
		} else */ $this->_metatype = $value;
		return $this;
	}

	public function getMetatype()
	{
		return $this->_metatype;
	}

	public function setDisciplineID($value)
	{
		/* if ( $value === null ) {
			$this->_disciplineID = 'NULL';
		} else */ $this->_disciplineID = $value;
		return $this;
	}

	public function getDisciplineID()
	{
		return $this->_disciplineID;
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


	public function setCategoryID($value)
	{
		/* if ( $value === null ) {
			$this->_categoryID = 'NULL';
		} else */ $this->_categoryID = $value;
		return $this;
	}

	public function getCategoryID()
	{
		return $this->_categoryID;
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

	public function setLinks($value)
	{
		/* if ( $value === null ) {
			$this->_links = 'NULL';
		} else */ $this->_links = $value;
		return $this;
	}

	public function getLinks()
	{
		return $this->_links;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_ApplicationsMapper());
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
		$XML = "<Application>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_abstract !== null) $XML .= "<abstract>".recode_string("utf8..xml",$this->_abstract)."</abstract>\n";
		if ($this->_statusID !== null) $XML .= "<statusID>".$this->_statusID."</statusID>\n";
		if ( $recursive ) if ( $this->_status === null ) $this->getStatus();
		if ( ! ($this->_status === null) ) $XML .= $this->_status->toXML();
		if ($this->_dateAdded !== null) $XML .= "<dateAdded>".recode_string("utf8..xml",$this->_dateAdded)."</dateAdded>\n";
		if ($this->_addedBy !== null) $XML .= "<addedBy>".$this->_addedBy."</addedBy>\n";
		if ( $recursive ) if ( $this->_researcher === null ) $this->getResearcher();
		if ( ! ($this->_researcher === null) ) $XML .= $this->_researcher->toXML();
		if ($this->_respect !== null) $XML .= "<respect>".$this->_respect."</respect>\n";
		if ($this->_tool !== null) $XML .= "<tool>".$this->_tool."</tool>\n";
		if ($this->_guid !== null) $XML .= "<guid>".recode_string("utf8..xml",$this->_guid)."</guid>\n";
		if ($this->_keywords !== null) $XML .= "<keywords>".recode_string("utf8..xml",$this->_keywords)."</keywords>\n";
		if ($this->_lastUpdated !== null) $XML .= "<lastUpdated>".recode_string("utf8..xml",$this->_lastUpdated)."</lastUpdated>\n";
		if ($this->_rating !== null) $XML .= "<rating>".$this->_rating."</rating>\n";
		if ($this->_ratingCount !== null) $XML .= "<ratingCount>".$this->_ratingCount."</ratingCount>\n";
		if ($this->_moderated !== null) $XML .= "<moderated>".$this->_moderated."</moderated>\n";
		if ($this->_tagPolicy !== null) $XML .= "<tagPolicy>".$this->_tagPolicy."</tagPolicy>\n";
		if ($this->_deleted !== null) $XML .= "<deleted>".$this->_deleted."</deleted>\n";
		if ($this->_metatype !== null) $XML .= "<metatype>".$this->_metatype."</metatype>\n";
		if ($this->_disciplineID !== null) $XML .= "<disciplineID>".$this->_disciplineID."</disciplineID>\n";
		if ($this->_ownerID !== null) $XML .= "<ownerID>".$this->_ownerID."</ownerID>\n";
		if ( $recursive ) if ( $this->_owner === null ) $this->getOwner();
		if ( ! ($this->_owner === null) ) $XML .= $this->_owner->toXML();
		if ($this->_categoryID !== null) $XML .= "<categoryID>".$this->_categoryID."</categoryID>\n";
		if ($this->_hitcount !== null) $XML .= "<hitcount>".$this->_hitcount."</hitcount>\n";
		if ($this->_cname !== null) $XML .= "<cname>".recode_string("utf8..xml",$this->_cname)."</cname>\n";
		if ($this->_links !== null) $XML .= "<links>".recode_string("utf8..xml",$this->_links)."</links>\n";
		$XML .= "</Application>\n";
		return $XML;
	}
}

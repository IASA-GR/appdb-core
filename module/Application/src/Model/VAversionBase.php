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
class Default_Model_VAversionBase
{
	protected $_mapper;
	protected $_id;
	protected $_version;
	protected $_guID;
	protected $_notes;
	protected $_vappID;
	protected $_va;
	protected $_published;
	protected $_publishedbyID;
	protected $_publishedby;
	protected $_createdon;
	protected $_publishedon;
	protected $_enabledon;
	protected $_enabledbyID;
	protected $_enabledby;
	protected $_expireson;
	protected $_enabled;
	protected $_archived;
	protected $_status;
	protected $_archivedon;
	protected $_submissionID;
	protected $_isexternal;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VAversion property: '$name'");
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
			throw new Exception("Invalid VAversion property: '$name'");
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

	public function setVersion($value)
	{
		/* if ( $value === null ) {
			$this->_version = 'NULL';
		} else */ $this->_version = $value;
		return $this;
	}

	public function getVersion()
	{
		return $this->_version;
	}

	public function setGuID($value)
	{
		/* if ( $value === null ) {
			$this->_guID = 'NULL';
		} else */ $this->_guID = $value;
		return $this;
	}

	public function getGuID()
	{
		return $this->_guID;
	}

	public function setNotes($value)
	{
		/* if ( $value === null ) {
			$this->_notes = 'NULL';
		} else */ $this->_notes = $value;
		return $this;
	}

	public function getNotes()
	{
		return $this->_notes;
	}

	public function setVappID($value)
	{
		/* if ( $value === null ) {
			$this->_vappID = 'NULL';
		} else */ $this->_vappID = $value;
		return $this;
	}

	public function getVappID()
	{
		return $this->_vappID;
	}

	public function getVa()
	{
		if ( $this->_va === null ) {
			$VAs = new Default_Model_VAs();
			$VAs->filter->id->equals($this->getVappID());
			if ($VAs->count() > 0) $this->_va = $VAs->items[0];
		}
		return $this->_va;
	}

	public function setVa($value)
	{
		if ( $value === null ) {
			$this->setVappID(null);
		} else {
			$this->setVappID($value->getId());
		}
	}


	public function setPublished($value)
	{
		/* if ( $value === null ) {
			$this->_published = 'NULL';
		} else */ $this->_published = $value;
		return $this;
	}

	public function getPublished()
	{
		$v = $this->_published;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setPublishedByID($value)
	{
		/* if ( $value === null ) {
			$this->_publishedbyID = 'NULL';
		} else */ $this->_publishedbyID = $value;
		return $this;
	}

	public function getPublishedByID()
	{
		return $this->_publishedbyID;
	}

	public function getPublishedBy()
	{
		if ( $this->_publishedby === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getPublishedByID());
			if ($Researchers->count() > 0) $this->_publishedby = $Researchers->items[0];
		}
		return $this->_publishedby;
	}

	public function setPublishedBy($value)
	{
		if ( $value === null ) {
			$this->setPublishedByID(null);
		} else {
			$this->setPublishedByID($value->getId());
		}
	}

	public function setCreatedon($value)
	{
		/* if ( $value === null ) {
			$this->_createdon = 'NULL';
		} else */ $this->_createdon = $value;
		return $this;
	}

	public function getCreatedon()
	{
		return $this->_createdon;
	}

	public function setPublishedOn($value)
	{
		/* if ( $value === null ) {
			$this->_publishedon = 'NULL';
		} else */ $this->_publishedon = $value;
		return $this;
	}

	public function getPublishedOn()
	{
		return $this->_publishedon;
	}

	public function setEnabledOn($value)
	{
		/* if ( $value === null ) {
			$this->_enabledon = 'NULL';
		} else */ $this->_enabledon = $value;
		return $this;
	}

	public function getEnabledOn()
	{
		return $this->_enabledon;
	}

	public function setEnabledByID($value)
	{
		/* if ( $value === null ) {
			$this->_enabledbyID = 'NULL';
		} else */ $this->_enabledbyID = $value;
		return $this;
	}

	public function getEnabledByID()
	{
		return $this->_enabledbyID;
	}

	public function getEnabledBy()
	{
		if ( $this->_enabledby === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getEnabledByID());
			if ($Researchers->count() > 0) $this->_enabledby = $Researchers->items[0];
		}
		return $this->_enabledby;
	}

	public function setEnabledBy($value)
	{
		if ( $value === null ) {
			$this->setEnabledByID(null);
		} else {
			$this->setEnabledByID($value->getId());
		}
	}


	public function setExpireson($value)
	{
		/* if ( $value === null ) {
			$this->_expireson = 'NULL';
		} else */ $this->_expireson = $value;
		return $this;
	}

	public function getExpireson()
	{
		return $this->_expireson;
	}

	public function setEnabled($value)
	{
		/* if ( $value === null ) {
			$this->_enabled = 'NULL';
		} else */ $this->_enabled = $value;
		return $this;
	}

	public function getEnabled()
	{
		$v = $this->_enabled;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setArchived($value)
	{
		/* if ( $value === null ) {
			$this->_archived = 'NULL';
		} else */ $this->_archived = $value;
		return $this;
	}

	public function getArchived()
	{
		$v = $this->_archived;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setStatus($value)
	{
		/* if ( $value === null ) {
			$this->_status = 'NULL';
		} else */ $this->_status = $value;
		return $this;
	}

	public function getStatus()
	{
		return $this->_status;
	}

	public function setArchivedon($value)
	{
		/* if ( $value === null ) {
			$this->_archivedon = 'NULL';
		} else */ $this->_archivedon = $value;
		return $this;
	}

	public function getArchivedon()
	{
		return $this->_archivedon;
	}

	public function setSubmissionID($value)
	{
		/* if ( $value === null ) {
			$this->_submissionID = 'NULL';
		} else */ $this->_submissionID = $value;
		return $this;
	}

	public function getSubmissionID()
	{
		return $this->_submissionID;
	}

	public function setIsexternal($value)
	{
		/* if ( $value === null ) {
			$this->_isexternal = 'NULL';
		} else */ $this->_isexternal = $value;
		return $this;
	}

	public function getIsexternal()
	{
		$v = $this->_isexternal;
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
			$this->setMapper(new Default_Model_VAversionsMapper());
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
		$XML = "<VAversion>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_version !== null) $XML .= "<version>".recode_string("utf8..xml",$this->_version)."</version>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		if ($this->_notes !== null) $XML .= "<notes>".recode_string("utf8..xml",$this->_notes)."</notes>\n";
		if ($this->_vappID !== null) $XML .= "<vappID>".$this->_vappID."</vappID>\n";
		if ( $recursive ) if ( $this->_va === null ) $this->getVa();
		if ( ! ($this->_va === null) ) $XML .= $this->_va->toXML();
		if ($this->_published !== null) $XML .= "<published>".$this->_published."</published>\n";
		if ($this->_publishedon !== null) $XML .= "<publishedon>".$this->_publishedon."</publishedon>\n";
		if ($this->_publishedbyID !== null) $XML .= "<publishedbyID>".$this->_publishedbyID."</publishedbyID>\n";
		if ( $recursive ) if ( $this->_publishedby === null ) $this->getPublishedBy();
		if ( ! ($this->_publishedby === null) ) $XML .= $this->_publishedby->toXML();
		if ($this->_createdon !== null) $XML .= "<createdon>".recode_string("utf8..xml",$this->_createdon)."</createdon>\n";
		if ($this->_expireson !== null) $XML .= "<expireson>".recode_string("utf8..xml",$this->_expireson)."</expireson>\n";
		if ($this->_enabled !== null) $XML .= "<enabled>".$this->_enabled."</enabled>\n";
		if ($this->_enabledon !== null) $XML .= "<enabledon>".$this->_enabledon."</enabledon>\n";
		if ($this->_enabledbyID !== null) $XML .= "<enabledbyID>".$this->_enabledbyID."</enabledbyID>\n";
		if ( $recursive ) if ( $this->_enabledby === null ) $this->getenabledBy();
		if ( ! ($this->_enabledby === null) ) $XML .= $this->_enabledby->toXML();
		if ($this->_archived !== null) $XML .= "<archived>".$this->_archived."</archived>\n";
		if ($this->_status !== null) $XML .= "<status>".recode_string("utf8..xml",$this->_status)."</status>\n";
		if ($this->_archivedon !== null) $XML .= "<archivedon>".recode_string("utf8..xml",$this->_archivedon)."</archivedon>\n";
		if ($this->_submissionID !== null) $XML .= "<submissionID>".$this->_submissionID."</submissionID>\n";
		if ($this->_isexternal !== null) $XML .= "<isexternal>".$this->_isexternal."</isexternal>\n";
		$XML .= "</VAversion>\n";
		return $XML;
	}
}

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
class Default_Model_VMIinstanceBase
{
	protected $_mapper;
	protected $_id;
	protected $_size;
	protected $_uri;
	protected $_version;
	protected $_checksum;
	protected $_checksumFunc;
	protected $_notes;
	protected $_guID;
	protected $_addedOn;
	protected $_addedbyID;
	protected $_addedBy;
	protected $_vmiflavourID;
	protected $_flavour;
	protected $_autoIntegrity;
	protected $_coreMinimum;
	protected $_RAMminimum;
	protected $_lastUpdatedByID;
	protected $_lastUpdatedBy;
	protected $_lastUpdatedOn;
	protected $_description;
	protected $_title;
	protected $_integrityStatus;
	protected $_integrityMessage;
	protected $_RAMrecommend;
	protected $_coreRecommend;
	protected $_accessinfo;
	protected $_enabled;
	protected $_initialsize;
	protected $_initialchecksum;
	protected $_ovfurl;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VMIinstance property: '$name'");
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
			throw new Exception("Invalid VMIinstance property: '$name'");
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

	public function setSize($value)
	{
		/* if ( $value === null ) {
			$this->_size = 'NULL';
		} else */ $this->_size = $value;
		return $this;
	}

	public function getSize()
	{
		return $this->_size;
	}

	public function setUri($value)
	{
		/* if ( $value === null ) {
			$this->_uri = 'NULL';
		} else */ $this->_uri = $value;
		return $this;
	}

	public function getUri()
	{
		return $this->_uri;
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

	public function setChecksum($value)
	{
		/* if ( $value === null ) {
			$this->_checksum = 'NULL';
		} else */ $this->_checksum = $value;
		return $this;
	}

	public function getChecksum()
	{
		return $this->_checksum;
	}

	public function setChecksumFunc($value)
	{
		/* if ( $value === null ) {
			$this->_checksumFunc = 'NULL';
		} else */ $this->_checksumFunc = $value;
		return $this;
	}

	public function getChecksumFunc()
	{
		return $this->_checksumFunc;
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

	public function setAddedbyID($value)
	{
		/* if ( $value === null ) {
			$this->_addedbyID = 'NULL';
		} else */ $this->_addedbyID = $value;
		return $this;
	}

	public function getAddedbyID()
	{
		return $this->_addedbyID;
	}

	public function getAddedBy()
	{
		if ( $this->_addedBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getAddedbyID());
			if ($Researchers->count() > 0) $this->_addedBy = $Researchers->items[0];
		}
		return $this->_addedBy;
	}

	public function setAddedBy($value)
	{
		if ( $value === null ) {
			$this->setAddedbyID(null);
		} else {
			$this->setAddedbyID($value->getId());
		}
	}


	public function setVmiflavourID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiflavourID = 'NULL';
		} else */ $this->_vmiflavourID = $value;
		return $this;
	}

	public function getVmiflavourID()
	{
		return $this->_vmiflavourID;
	}

	public function getFlavour()
	{
		if ( $this->_flavour === null ) {
			$VMIflavours = new Default_Model_VMIflavours();
			$VMIflavours->filter->id->equals($this->getVmiflavourID());
			if ($VMIflavours->count() > 0) $this->_flavour = $VMIflavours->items[0];
		}
		return $this->_flavour;
	}

	public function setFlavour($value)
	{
		if ( $value === null ) {
			$this->setVmiflavourID(null);
		} else {
			$this->setVmiflavourID($value->getId());
		}
	}


	public function setAutoIntegrity($value)
	{
		/* if ( $value === null ) {
			$this->_autoIntegrity = 'NULL';
		} else */ $this->_autoIntegrity = $value;
		return $this;
	}

	public function getAutoIntegrity()
	{
		$v = $this->_autoIntegrity;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setCoreMinimum($value)
	{
		/* if ( $value === null ) {
			$this->_coreMinimum = 'NULL';
		} else */ $this->_coreMinimum = $value;
		return $this;
	}

	public function getCoreMinimum()
	{
		return $this->_coreMinimum;
	}

	public function setRAMminimum($value)
	{
		/* if ( $value === null ) {
			$this->_RAMminimum = 'NULL';
		} else */ $this->_RAMminimum = $value;
		return $this;
	}

	public function getRAMminimum()
	{
		return $this->_RAMminimum;
	}

	public function setLastUpdatedByID($value)
	{
		/* if ( $value === null ) {
			$this->_lastUpdatedByID = 'NULL';
		} else */ $this->_lastUpdatedByID = $value;
		return $this;
	}

	public function getLastUpdatedByID()
	{
		return $this->_lastUpdatedByID;
	}

	public function getLastUpdatedBy()
	{
		if ( $this->_lastUpdatedBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getLastUpdatedByID());
			if ($Researchers->count() > 0) $this->_lastUpdatedBy = $Researchers->items[0];
		}
		return $this->_lastUpdatedBy;
	}

	public function setLastUpdatedBy($value)
	{
		if ( $value === null ) {
			$this->setLastUpdatedByID(null);
		} else {
			$this->setLastUpdatedByID($value->getId());
		}
	}


	public function setLastUpdatedOn($value)
	{
		/* if ( $value === null ) {
			$this->_lastUpdatedOn = 'NULL';
		} else */ $this->_lastUpdatedOn = $value;
		return $this;
	}

	public function getLastUpdatedOn()
	{
		return $this->_lastUpdatedOn;
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

	public function setIntegrityStatus($value)
	{
		/* if ( $value === null ) {
			$this->_integrityStatus = 'NULL';
		} else */ $this->_integrityStatus = $value;
		return $this;
	}

	public function getIntegrityStatus()
	{
		return $this->_integrityStatus;
	}

	public function setIntegrityMessage($value)
	{
		/* if ( $value === null ) {
			$this->_integrityMessage = 'NULL';
		} else */ $this->_integrityMessage = $value;
		return $this;
	}

	public function getIntegrityMessage()
	{
		return $this->_integrityMessage;
	}

	public function setRAMrecommend($value)
	{
		/* if ( $value === null ) {
			$this->_RAMrecommend = 'NULL';
		} else */ $this->_RAMrecommend = $value;
		return $this;
	}

	public function getRAMrecommend()
	{
		return $this->_RAMrecommend;
	}

	public function setCoreRecommend($value)
	{
		/* if ( $value === null ) {
			$this->_coreRecommend = 'NULL';
		} else */ $this->_coreRecommend = $value;
		return $this;
	}

	public function getCoreRecommend()
	{
		return $this->_coreRecommend;
	}

	public function setAccessinfo($value)
	{
		/* if ( $value === null ) {
			$this->_accessinfo = 'NULL';
		} else */ $this->_accessinfo = $value;
		return $this;
	}

	public function getAccessinfo()
	{
		return $this->_accessinfo;
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

	public function setOVFURL($value)
	{
		/* if ( $value === null ) {
			$this->_initialsize = 'NULL';
		} else */ $this->_ovfurl = $value;
		return $this;
	}

	public function getOVFURL()
	{
		return $this->_ovfurl;
	}

	public function setInitialsize($value)
	{
		/* if ( $value === null ) {
			$this->_initialsize = 'NULL';
		} else */ $this->_initialsize = $value;
		return $this;
	}

	public function getInitialsize()
	{
		return $this->_initialsize;
	}

	public function setInitialchecksum($value)
	{
		/* if ( $value === null ) {
			$this->_initialchecksum = 'NULL';
		} else */ $this->_initialchecksum = $value;
		return $this;
	}

	public function getInitialchecksum()
	{
		return $this->_initialchecksum;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_VMIinstancesMapper());
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
		$XML = "<VMIinstance>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_size !== null) $XML .= "<size>".$this->_size."</size>\n";
		if ($this->_uri !== null) $XML .= "<uri>".recode_string("utf8..xml",$this->_uri)."</uri>\n";
		if ($this->_version !== null) $XML .= "<version>".recode_string("utf8..xml",$this->_version)."</version>\n";
		if ($this->_checksum !== null) $XML .= "<checksum>".recode_string("utf8..xml",$this->_checksum)."</checksum>\n";
		if ($this->_checksumFunc !== null) $XML .= "<checksumFunc>".$this->_checksumFunc."</checksumFunc>\n";
		if ($this->_notes !== null) $XML .= "<notes>".recode_string("utf8..xml",$this->_notes)."</notes>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		if ($this->_addedOn !== null) $XML .= "<addedOn>".recode_string("utf8..xml",$this->_addedOn)."</addedOn>\n";
		if ($this->_addedbyID !== null) $XML .= "<addedbyID>".$this->_addedbyID."</addedbyID>\n";
		if ( $recursive ) if ( $this->_addedBy === null ) $this->getAddedBy();
		if ( ! ($this->_addedBy === null) ) $XML .= $this->_addedBy->toXML();
		if ($this->_vmiflavourID !== null) $XML .= "<vmiflavourID>".$this->_vmiflavourID."</vmiflavourID>\n";
		if ( $recursive ) if ( $this->_flavour === null ) $this->getFlavour();
		if ( ! ($this->_flavour === null) ) $XML .= $this->_flavour->toXML();
		if ($this->_autoIntegrity !== null) $XML .= "<autoIntegrity>".$this->_autoIntegrity."</autoIntegrity>\n";
		if ($this->_coreMinimum !== null) $XML .= "<coreMinimum>".$this->_coreMinimum."</coreMinimum>\n";
		if ($this->_RAMminimum !== null) $XML .= "<RAMminimum>".$this->_RAMminimum."</RAMminimum>\n";
		if ($this->_lastUpdatedByID !== null) $XML .= "<lastUpdatedByID>".$this->_lastUpdatedByID."</lastUpdatedByID>\n";
		if ( $recursive ) if ( $this->_lastUpdatedBy === null ) $this->getLastUpdatedBy();
		if ( ! ($this->_lastUpdatedBy === null) ) $XML .= $this->_lastUpdatedBy->toXML();
		if ($this->_lastUpdatedOn !== null) $XML .= "<lastUpdatedOn>".recode_string("utf8..xml",$this->_lastUpdatedOn)."</lastUpdatedOn>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_title !== null) $XML .= "<title>".recode_string("utf8..xml",$this->_title)."</title>\n";
		if ($this->_integrityStatus !== null) $XML .= "<integrityStatus>".recode_string("utf8..xml",$this->_integrityStatus)."</integrityStatus>\n";
		if ($this->_integrityMessage !== null) $XML .= "<integrityMessage>".recode_string("utf8..xml",$this->_integrityMessage)."</integrityMessage>\n";
		if ($this->_RAMrecommend !== null) $XML .= "<RAMrecommend>".$this->_RAMrecommend."</RAMrecommend>\n";
		if ($this->_coreRecommend !== null) $XML .= "<coreRecommend>".$this->_coreRecommend."</coreRecommend>\n";
		if ($this->_accessinfo !== null) $XML .= "<accessinfo>".recode_string("utf8..xml",$this->_accessinfo)."</accessinfo>\n";
		if ($this->_enabled !== null) $XML .= "<enabled>".$this->_enabled."</enabled>\n";
		if ($this->_initialsize !== null) $XML .= "<initialsize>".recode_string("utf8..xml",$this->_initialsize)."</initialsize>\n";
		if ($this->_initialchecksum !== null) $XML .= "<initialchecksum>".recode_string("utf8..xml",$this->_initialchecksum)."</initialchecksum>\n";
		if ($this->_ovfurl !== null) $XML .= "<ovfurl>".recode_string("utf8..xml",$this->_ovfurl)."</ovfurl>\n";
		$XML .= "</VMIinstance>\n";
		return $XML;
	}
}

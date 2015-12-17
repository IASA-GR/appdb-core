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
class Default_Model_DatasetLocationBase
{
	protected $_mapper;
	protected $_id;
	protected $_addedByID;
	protected $_addedBy;
	protected $_addedon;
	protected $_uri;
	protected $_isMaster;
	protected $_exchangeFormatID;
	protected $_exchangeFormat;
	protected $_connectionTypeID;
	protected $_connectionType;
	protected $_isPublic;
	protected $_organizationID;
	protected $_organization;
	protected $_siteID;
	protected $_site;
	protected $_notes;
	protected $_datasetVersionID;
	protected $_datasetVersion;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid DatasetLocation property: '$name'");
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
			throw new Exception("Invalid DatasetLocation property: '$name'");
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


	public function setAddedon($value)
	{
		/* if ( $value === null ) {
			$this->_addedon = 'NULL';
		} else */ $this->_addedon = $value;
		return $this;
	}

	public function getAddedon()
	{
		return $this->_addedon;
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

	public function setIsMaster($value)
	{
		/* if ( $value === null ) {
			$this->_isMaster = 'NULL';
		} else */ $this->_isMaster = $value;
		return $this;
	}

	public function getIsMaster()
	{
		$v = $this->_isMaster;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setExchangeFormatID($value)
	{
		/* if ( $value === null ) {
			$this->_exchangeFormatID = 'NULL';
		} else */ $this->_exchangeFormatID = $value;
		return $this;
	}

	public function getExchangeFormatID()
	{
		return $this->_exchangeFormatID;
	}

	public function getExchangeFormat()
	{
		if ( $this->_exchangeFormat === null ) {
			$DatasetExchangeFormats = new Default_Model_DatasetExchangeFormats();
			$DatasetExchangeFormats->filter->id->equals($this->getExchangeFormatID());
			if ($DatasetExchangeFormats->count() > 0) $this->_exchangeFormat = $DatasetExchangeFormats->items[0];
		}
		return $this->_exchangeFormat;
	}

	public function setExchangeFormat($value)
	{
		if ( $value === null ) {
			$this->setExchangeFormatID(null);
		} else {
			$this->setExchangeFormatID($value->getId());
		}
	}


	public function setConnectionTypeID($value)
	{
		/* if ( $value === null ) {
			$this->_connectionTypeID = 'NULL';
		} else */ $this->_connectionTypeID = $value;
		return $this;
	}

	public function getConnectionTypeID()
	{
		return $this->_connectionTypeID;
	}

	public function getConnectionType()
	{
		if ( $this->_connectionType === null ) {
			$DatasetConnTypes = new Default_Model_DatasetConnTypes();
			$DatasetConnTypes->filter->id->equals($this->getConnectionTypeID());
			if ($DatasetConnTypes->count() > 0) $this->_connectionType = $DatasetConnTypes->items[0];
		}
		return $this->_connectionType;
	}

	public function setConnectionType($value)
	{
		if ( $value === null ) {
			$this->setConnectionTypeID(null);
		} else {
			$this->setConnectionTypeID($value->getId());
		}
	}


	public function setIsPublic($value)
	{
		/* if ( $value === null ) {
			$this->_isPublic = 'NULL';
		} else */ $this->_isPublic = $value;
		return $this;
	}

	public function getIsPublic()
	{
		$v = $this->_isPublic;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setOrganizationID($value)
	{
		/* if ( $value === null ) {
			$this->_organizationID = 'NULL';
		} else */ $this->_organizationID = $value;
		return $this;
	}

	public function getOrganizationID()
	{
		return $this->_organizationID;
	}

	public function getOrganization()
	{
		if ( $this->_organization === null ) {
			$Organizations = new Default_Model_Organizations();
			$Organizations->filter->id->equals($this->getOrganizationID());
			if ($Organizations->count() > 0) $this->_organization = $Organizations->items[0];
		}
		return $this->_organization;
	}

	public function setOrganization($value)
	{
		if ( $value === null ) {
			$this->setOrganizationID(null);
		} else {
			$this->setOrganizationID($value->getId());
		}
	}


	public function setSiteID($value)
	{
		/* if ( $value === null ) {
			$this->_siteID = 'NULL';
		} else */ $this->_siteID = $value;
		return $this;
	}

	public function getSiteID()
	{
		return $this->_siteID;
	}

	public function getSite()
	{
		if ( $this->_site === null ) {
			$Sites = new Default_Model_Sites();
			$Sites->filter->id->equals($this->getSiteID());
			if ($Sites->count() > 0) $this->_site = $Sites->items[0];
		}
		return $this->_site;
	}

	public function setSite($value)
	{
		if ( $value === null ) {
			$this->setSiteID(null);
		} else {
			$this->setSiteID($value->getId());
		}
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

	public function setDatasetVersionID($value)
	{
		/* if ( $value === null ) {
			$this->_datasetVersionID = 'NULL';
		} else */ $this->_datasetVersionID = $value;
		return $this;
	}

	public function getDatasetVersionID()
	{
		return $this->_datasetVersionID;
	}

	public function setDatasetVersion($value)
	{
		if ( $value === null ) {
			$this->setDatasetVersionID(null);
		} else {
			$this->setDatasetVersionID($value->getId());
		}
	}

	public function getDatasetVersion()
	{
		if ( $this->_datasetVersion === null ) {
			$dvers= new Default_Model_DatasetVersions();
			$dvers->filter->id->equals($this->getDatasetVersionID());
			if ($dvers->count() > 0) $this->_datasetVersion = $dvers->items[0];
		}
		return $this->_datasetVersion;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_DatasetLocationsMapper());
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
		$XML = "<DatasetLocation>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_addedByID !== null) $XML .= "<addedByID>".$this->_addedByID."</addedByID>\n";
		if ( $recursive ) if ( $this->_addedBy === null ) $this->getAddedBy();
		if ( ! ($this->_addedBy === null) ) $XML .= $this->_addedBy->toXML();
		if ($this->_addedon !== null) $XML .= "<addedon>".recode_string("utf8..xml",$this->_addedon)."</addedon>\n";
		if ($this->_uri !== null) $XML .= "<uri>".recode_string("utf8..xml",$this->_uri)."</uri>\n";
		if ($this->_isMaster !== null) $XML .= "<isMaster>".$this->_isMaster."</isMaster>\n";
		if ($this->_exchangeFormatID !== null) $XML .= "<exchangeFormatID>".$this->_exchangeFormatID."</exchangeFormatID>\n";
		if ( $recursive ) if ( $this->_exchangeFormat === null ) $this->getExchangeFormat();
		if ( ! ($this->_exchangeFormat === null) ) $XML .= $this->_exchangeFormat->toXML();
		if ($this->_connectionTypeID !== null) $XML .= "<connectionTypeID>".$this->_connectionTypeID."</connectionTypeID>\n";
		if ( $recursive ) if ( $this->_connectionType === null ) $this->getConnectionType();
		if ( ! ($this->_connectionType === null) ) $XML .= $this->_connectionType->toXML();
		if ($this->_isPublic !== null) $XML .= "<isPublic>".$this->_isPublic."</isPublic>\n";
		if ($this->_organizationID !== null) $XML .= "<organizationID>".$this->_organizationID."</organizationID>\n";
		if ( $recursive ) if ( $this->_organization === null ) $this->getOrganization();
		if ( ! ($this->_organization === null) ) $XML .= $this->_organization->toXML();
		if ($this->_siteID !== null) $XML .= "<siteID>".$this->_siteID."</siteID>\n";
		if ( $recursive ) if ( $this->_site === null ) $this->getSite();
		if ( ! ($this->_site === null) ) $XML .= $this->_site->toXML();
		if ($this->_notes !== null) $XML .= "<notes>".recode_string("utf8..xml",$this->_notes)."</notes>\n";
		if ($this->_datasetVersionID !== null) $XML .= "<datasetVersionID>".$this->_datasetVersionID."</datasetVersionID>\n";
		$XML .= "</DatasetLocation>\n";
		return $XML;
	}
}

<?php
class Repository_Model_MetaProductRepoAreaBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_swId;
	protected $_swName;
	protected $_description;
	protected $_installationNotes;
	protected $_additionalDetails;
	protected $_yumRepofileId;
	protected $_aptRepofileId;
	protected $_knownIssues;
	protected $_timestampInserted;
	protected $_timestampLastUpdated;
	protected $_timestampLastProductionBuild;
	protected $_insertedBy;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid MetaProductRepoArea property: '$name'");
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
			throw new Exception("Invalid MetaProductRepoArea property: '$name'");
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

	public function setSwId($value)
	{
		/* if ( $value === null ) {
			$this->_swId = 'NULL';
		} else */ $this->_swId = $value;
		return $this;
	}

	public function getSwId()
	{
		return $this->_swId;
	}

	public function setSwName($value)
	{
		/* if ( $value === null ) {
			$this->_swName = 'NULL';
		} else */ $this->_swName = $value;
		return $this;
	}

	public function getSwName()
	{
		return $this->_swName;
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

	public function setInstallationNotes($value)
	{
		/* if ( $value === null ) {
			$this->_installationNotes = 'NULL';
		} else */ $this->_installationNotes = $value;
		return $this;
	}

	public function getInstallationNotes()
	{
		return $this->_installationNotes;
	}

	public function setAdditionalDetails($value)
	{
		/* if ( $value === null ) {
			$this->_additionalDetails = 'NULL';
		} else */ $this->_additionalDetails = $value;
		return $this;
	}

	public function getAdditionalDetails()
	{
		return $this->_additionalDetails;
	}

	public function setYumRepofileId($value)
	{
		/* if ( $value === null ) {
			$this->_yumRepofileId = 'NULL';
		} else */ $this->_yumRepofileId = $value;
		return $this;
	}

	public function getYumRepofileId()
	{
		return $this->_yumRepofileId;
	}

	public function setAptRepofileId($value)
	{
		/* if ( $value === null ) {
			$this->_aptRepofileId = 'NULL';
		} else */ $this->_aptRepofileId = $value;
		return $this;
	}

	public function getAptRepofileId()
	{
		return $this->_aptRepofileId;
	}

	public function setKnownIssues($value)
	{
		/* if ( $value === null ) {
			$this->_knownIssues = 'NULL';
		} else */ $this->_knownIssues = $value;
		return $this;
	}

	public function getKnownIssues()
	{
		return $this->_knownIssues;
	}

	public function setTimestampInserted($value)
	{
		/* if ( $value === null ) {
			$this->_timestampInserted = 'NULL';
		} else */ $this->_timestampInserted = $value;
		return $this;
	}

	public function getTimestampInserted()
	{
		return $this->_timestampInserted;
	}

	public function setTimestampLastUpdated($value)
	{
		/* if ( $value === null ) {
			$this->_timestampLastUpdated = 'NULL';
		} else */ $this->_timestampLastUpdated = $value;
		return $this;
	}

	public function getTimestampLastUpdated()
	{
		return $this->_timestampLastUpdated;
	}

	public function setTimestampLastProductionBuild($value)
	{
		/* if ( $value === null ) {
			$this->_timestampLastProductionBuild = 'NULL';
		} else */ $this->_timestampLastProductionBuild = $value;
		return $this;
	}

	public function getTimestampLastProductionBuild()
	{
		return $this->_timestampLastProductionBuild;
	}

	public function setInsertedBy($value)
	{
		/* if ( $value === null ) {
			$this->_insertedBy = 'NULL';
		} else */ $this->_insertedBy = $value;
		return $this;
	}

	public function getInsertedBy()
	{
		return $this->_insertedBy;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Repository_Model_MetaProductRepoAreasMapper());
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
		$XML = "<MetaProductRepoArea>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_swId !== null) $XML .= "<swId>".$this->_swId."</swId>\n";
		if ($this->_swName !== null) $XML .= "<swName>".recode_string("utf8..xml",$this->_swName)."</swName>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_installationNotes !== null) $XML .= "<installationNotes>".recode_string("utf8..xml",$this->_installationNotes)."</installationNotes>\n";
		if ($this->_additionalDetails !== null) $XML .= "<additionalDetails>".recode_string("utf8..xml",$this->_additionalDetails)."</additionalDetails>\n";
		if ($this->_yumRepofileId !== null) $XML .= "<yumRepofileId>".$this->_yumRepofileId."</yumRepofileId>\n";
		if ($this->_aptRepofileId !== null) $XML .= "<aptRepofileId>".$this->_aptRepofileId."</aptRepofileId>\n";
		if ($this->_knownIssues !== null) $XML .= "<knownIssues>".recode_string("utf8..xml",$this->_knownIssues)."</knownIssues>\n";
		if ($this->_timestampInserted !== null) $XML .= "<timestampInserted>".recode_string("utf8..xml",$this->_timestampInserted)."</timestampInserted>\n";
		if ($this->_timestampLastUpdated !== null) $XML .= "<timestampLastUpdated>".recode_string("utf8..xml",$this->_timestampLastUpdated)."</timestampLastUpdated>\n";
		if ($this->_timestampLastProductionBuild !== null) $XML .= "<timestampLastProductionBuild>".recode_string("utf8..xml",$this->_timestampLastProductionBuild)."</timestampLastProductionBuild>\n";
		if ($this->_insertedBy !== null) $XML .= "<insertedBy>".$this->_insertedBy."</insertedBy>\n";
		$XML .= "</MetaProductRepoArea>\n";
		return $XML;
	}
}

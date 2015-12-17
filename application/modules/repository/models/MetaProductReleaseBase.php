<?php
class Repository_Model_MetaProductReleaseBase
{
	protected $_mapper;
	protected $_id;
	protected $_currentStateId;
	protected $_commRepoState;
	protected $_previousStateId;
	protected $_displayVersion;
	protected $_parentId;
	protected $_parentRelease;
	protected $_displayIndex;
	protected $_repoAreaId;
	protected $_repoArea;
	protected $_priority;
	protected $_description;
	protected $_technologyProvider;
	protected $_technologyProviderShortName;
	protected $_iSODate;
	protected $_incremental;
	protected $_majorVersion;
	protected $_minorVersion;
	protected $_updateVersion;
	protected $_revisionVersion;
	protected $_releaseNotes;
	protected $_changeLog;
	protected $_installationNotes;
	protected $_knownIssues;
	protected $_repositoryURL;
	protected $_releaseXML;
	protected $_qualityCriteriaVerificationReport;
	protected $_stageRolloutReport;
	protected $_additionalDetails;
	protected $_deleted;
	protected $_extraFld1;
	protected $_extraFld2;
	protected $_extraFld3;
	protected $_extraFld4;
	protected $_extraFld5;
	protected $_timestampInserted;
	protected $_timestampLastUpdated;
	protected $_timestampLastStateChange;
	protected $_timestampReleaseDate;
	protected $_timestampLastProductionBuild;
	protected $_insertedBy;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid MetaProductRelease property: '$name'");
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
			throw new Exception("Invalid MetaProductRelease property: '$name'");
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

	public function setCurrentStateId($value)
	{
		/* if ( $value === null ) {
			$this->_currentStateId = 'NULL';
		} else */ $this->_currentStateId = $value;
		return $this;
	}

	public function getCurrentStateId()
	{
		return $this->_currentStateId;
	}

	public function getCommRepoState()
	{
		if ( $this->_commRepoState === null ) {
			$CommRepoStates = new Repository_Model_CommRepoStates();
			$CommRepoStates->filter->id->equals($this->getCurrentStateId());
			if ($CommRepoStates->count() > 0) $this->_commRepoState = $CommRepoStates->items[0];
		}
		return $this->_commRepoState;
	}

	public function setCommRepoState($value)
	{
		if ( $value === null ) {
			$this->setCurrentStateId(null);
		} else {
			$this->setCurrentStateId($value->getId());
		}
	}


	public function setPreviousStateId($value)
	{
		/* if ( $value === null ) {
			$this->_previousStateId = 'NULL';
		} else */ $this->_previousStateId = $value;
		return $this;
	}

	public function getPreviousStateId()
	{
		return $this->_previousStateId;
	}

	public function setDisplayVersion($value)
	{
		/* if ( $value === null ) {
			$this->_displayVersion = 'NULL';
		} else */ $this->_displayVersion = $value;
		return $this;
	}

	public function getDisplayVersion()
	{
		return $this->_displayVersion;
	}

	public function setParentId($value)
	{
		/* if ( $value === null ) {
			$this->_parentId = 'NULL';
		} else */ $this->_parentId = $value;
		return $this;
	}

	public function getParentId()
	{
		return $this->_parentId;
	}

	public function getParentRelease()
	{
		if ( $this->_parentRelease === null ) {
			$MetaProductReleases = new Repository_Model_MetaProductReleases();
			$MetaProductReleases->filter->id->equals($this->getParentId());
			if ($MetaProductReleases->count() > 0) $this->_parentRelease = $MetaProductReleases->items[0];
		}
		return $this->_parentRelease;
	}

	public function setParentRelease($value)
	{
		if ( $value === null ) {
			$this->setParentId(null);
		} else {
			$this->setParentId($value->getId());
		}
	}


	public function setDisplayIndex($value)
	{
		/* if ( $value === null ) {
			$this->_displayIndex = 'NULL';
		} else */ $this->_displayIndex = $value;
		return $this;
	}

	public function getDisplayIndex()
	{
		return $this->_displayIndex;
	}

	public function setRepoAreaId($value)
	{
		/* if ( $value === null ) {
			$this->_repoAreaId = 'NULL';
		} else */ $this->_repoAreaId = $value;
		return $this;
	}

	public function getRepoAreaId()
	{
		return $this->_repoAreaId;
	}

	public function getRepoArea()
	{
		if ( $this->_repoArea === null ) {
			$MetaProductRepoAreas = new Repository_Model_MetaProductRepoAreas();
			$MetaProductRepoAreas->filter->id->equals($this->getRepoAreaId());
			if ($MetaProductRepoAreas->count() > 0) $this->_repoArea = $MetaProductRepoAreas->items[0];
		}
		return $this->_repoArea;
	}

	public function setRepoArea($value)
	{
		if ( $value === null ) {
			$this->setRepoAreaId(null);
		} else {
			$this->setRepoAreaId($value->getId());
		}
	}


	public function setPriority($value)
	{
		/* if ( $value === null ) {
			$this->_priority = 'NULL';
		} else */ $this->_priority = $value;
		return $this;
	}

	public function getPriority()
	{
		return $this->_priority;
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

	public function setTechnologyProvider($value)
	{
		/* if ( $value === null ) {
			$this->_technologyProvider = 'NULL';
		} else */ $this->_technologyProvider = $value;
		return $this;
	}

	public function getTechnologyProvider()
	{
		return $this->_technologyProvider;
	}

	public function setTechnologyProviderShortName($value)
	{
		/* if ( $value === null ) {
			$this->_technologyProviderShortName = 'NULL';
		} else */ $this->_technologyProviderShortName = $value;
		return $this;
	}

	public function getTechnologyProviderShortName()
	{
		return $this->_technologyProviderShortName;
	}

	public function setISODate($value)
	{
		/* if ( $value === null ) {
			$this->_iSODate = 'NULL';
		} else */ $this->_iSODate = $value;
		return $this;
	}

	public function getISODate()
	{
		return $this->_iSODate;
	}

	public function setIncremental($value)
	{
		/* if ( $value === null ) {
			$this->_incremental = 'NULL';
		} else */ $this->_incremental = $value;
		return $this;
	}

	public function getIncremental()
	{
		$v = $this->_incremental;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setMajorVersion($value)
	{
		/* if ( $value === null ) {
			$this->_majorVersion = 'NULL';
		} else */ $this->_majorVersion = $value;
		return $this;
	}

	public function getMajorVersion()
	{
		return $this->_majorVersion;
	}

	public function setMinorVersion($value)
	{
		/* if ( $value === null ) {
			$this->_minorVersion = 'NULL';
		} else */ $this->_minorVersion = $value;
		return $this;
	}

	public function getMinorVersion()
	{
		return $this->_minorVersion;
	}

	public function setUpdateVersion($value)
	{
		/* if ( $value === null ) {
			$this->_updateVersion = 'NULL';
		} else */ $this->_updateVersion = $value;
		return $this;
	}

	public function getUpdateVersion()
	{
		return $this->_updateVersion;
	}

	public function setRevisionVersion($value)
	{
		/* if ( $value === null ) {
			$this->_revisionVersion = 'NULL';
		} else */ $this->_revisionVersion = $value;
		return $this;
	}

	public function getRevisionVersion()
	{
		return $this->_revisionVersion;
	}

	public function setReleaseNotes($value)
	{
		/* if ( $value === null ) {
			$this->_releaseNotes = 'NULL';
		} else */ $this->_releaseNotes = $value;
		return $this;
	}

	public function getReleaseNotes()
	{
		return $this->_releaseNotes;
	}

	public function setChangeLog($value)
	{
		/* if ( $value === null ) {
			$this->_changeLog = 'NULL';
		} else */ $this->_changeLog = $value;
		return $this;
	}

	public function getChangeLog()
	{
		return $this->_changeLog;
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

	public function setRepositoryURL($value)
	{
		/* if ( $value === null ) {
			$this->_repositoryURL = 'NULL';
		} else */ $this->_repositoryURL = $value;
		return $this;
	}

	public function getRepositoryURL()
	{
		return $this->_repositoryURL;
	}

	public function setReleaseXML($value)
	{
		/* if ( $value === null ) {
			$this->_releaseXML = 'NULL';
		} else */ $this->_releaseXML = $value;
		return $this;
	}

	public function getReleaseXML()
	{
		return $this->_releaseXML;
	}

	public function setQualityCriteriaVerificationReport($value)
	{
		/* if ( $value === null ) {
			$this->_qualityCriteriaVerificationReport = 'NULL';
		} else */ $this->_qualityCriteriaVerificationReport = $value;
		return $this;
	}

	public function getQualityCriteriaVerificationReport()
	{
		return $this->_qualityCriteriaVerificationReport;
	}

	public function setStageRolloutReport($value)
	{
		/* if ( $value === null ) {
			$this->_stageRolloutReport = 'NULL';
		} else */ $this->_stageRolloutReport = $value;
		return $this;
	}

	public function getStageRolloutReport()
	{
		return $this->_stageRolloutReport;
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

	public function setDeleted($value)
	{
		/* if ( $value === null ) {
			$this->_deleted = 'NULL';
		} else */ $this->_deleted = $value;
		return $this;
	}

	public function getDeleted()
	{
		return $this->_deleted;
	}

	public function setExtraFld1($value)
	{
		/* if ( $value === null ) {
			$this->_extraFld1 = 'NULL';
		} else */ $this->_extraFld1 = $value;
		return $this;
	}

	public function getExtraFld1()
	{
		return $this->_extraFld1;
	}

	public function setExtraFld2($value)
	{
		/* if ( $value === null ) {
			$this->_extraFld2 = 'NULL';
		} else */ $this->_extraFld2 = $value;
		return $this;
	}

	public function getExtraFld2()
	{
		return $this->_extraFld2;
	}

	public function setExtraFld3($value)
	{
		/* if ( $value === null ) {
			$this->_extraFld3 = 'NULL';
		} else */ $this->_extraFld3 = $value;
		return $this;
	}

	public function getExtraFld3()
	{
		return $this->_extraFld3;
	}

	public function setExtraFld4($value)
	{
		/* if ( $value === null ) {
			$this->_extraFld4 = 'NULL';
		} else */ $this->_extraFld4 = $value;
		return $this;
	}

	public function getExtraFld4()
	{
		return $this->_extraFld4;
	}

	public function setExtraFld5($value)
	{
		/* if ( $value === null ) {
			$this->_extraFld5 = 'NULL';
		} else */ $this->_extraFld5 = $value;
		return $this;
	}

	public function getExtraFld5()
	{
		return $this->_extraFld5;
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

	public function setTimestampLastStateChange($value)
	{
		/* if ( $value === null ) {
			$this->_timestampLastStateChange = 'NULL';
		} else */ $this->_timestampLastStateChange = $value;
		return $this;
	}

	public function getTimestampLastStateChange()
	{
		return $this->_timestampLastStateChange;
	}

	public function setTimestampReleaseDate($value)
	{
		/* if ( $value === null ) {
			$this->_timestampReleaseDate = 'NULL';
		} else */ $this->_timestampReleaseDate = $value;
		return $this;
	}

	public function getTimestampReleaseDate()
	{
		return $this->_timestampReleaseDate;
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
			$this->setMapper(new Repository_Model_MetaProductReleasesMapper());
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
		$XML = "<MetaProductRelease>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_currentStateId !== null) $XML .= "<currentStateId>".$this->_currentStateId."</currentStateId>\n";
		if ( $recursive ) if ( $this->_commRepoState === null ) $this->getCommRepoState();
		if ( ! ($this->_commRepoState === null) ) $XML .= $this->_commRepoState->toXML();
		if ($this->_previousStateId !== null) $XML .= "<previousStateId>".$this->_previousStateId."</previousStateId>\n";
		if ($this->_displayVersion !== null) $XML .= "<displayVersion>".recode_string("utf8..xml",$this->_displayVersion)."</displayVersion>\n";
		if ($this->_parentId !== null) $XML .= "<parentId>".$this->_parentId."</parentId>\n";
		if ( $recursive ) if ( $this->_parentRelease === null ) $this->getParentRelease();
		if ( ! ($this->_parentRelease === null) ) $XML .= $this->_parentRelease->toXML();
		if ($this->_displayIndex !== null) $XML .= "<displayIndex>".$this->_displayIndex."</displayIndex>\n";
		if ($this->_repoAreaId !== null) $XML .= "<repoAreaId>".$this->_repoAreaId."</repoAreaId>\n";
		if ( $recursive ) if ( $this->_repoArea === null ) $this->getRepoArea();
		if ( ! ($this->_repoArea === null) ) $XML .= $this->_repoArea->toXML();
		if ($this->_priority !== null) $XML .= "<priority>".$this->_priority."</priority>\n";
		if ($this->_description !== null) $XML .= "<description>".$this->_description."</description>\n";
		if ($this->_technologyProvider !== null) $XML .= "<technologyProvider>".recode_string("utf8..xml",$this->_technologyProvider)."</technologyProvider>\n";
		if ($this->_technologyProviderShortName !== null) $XML .= "<technologyProviderShortName>".recode_string("utf8..xml",$this->_technologyProviderShortName)."</technologyProviderShortName>\n";
		if ($this->_iSODate !== null) $XML .= "<iSODate>".$this->_iSODate."</iSODate>\n";
		if ($this->_incremental !== null) $XML .= "<incremental>".$this->_incremental."</incremental>\n";
		if ($this->_majorVersion !== null) $XML .= "<majorVersion>".$this->_majorVersion."</majorVersion>\n";
		if ($this->_minorVersion !== null) $XML .= "<minorVersion>".$this->_minorVersion."</minorVersion>\n";
		if ($this->_updateVersion !== null) $XML .= "<updateVersion>".$this->_updateVersion."</updateVersion>\n";
		if ($this->_revisionVersion !== null) $XML .= "<revisionVersion>".$this->_revisionVersion."</revisionVersion>\n";
		if ($this->_releaseNotes !== null) $XML .= "<releaseNotes>".$this->_releaseNotes."</releaseNotes>\n";
		if ($this->_changeLog !== null) $XML .= "<changeLog>".$this->_changeLog."</changeLog>\n";
		if ($this->_installationNotes !== null) $XML .= "<installationNotes>".$this->_installationNotes."</installationNotes>\n";
		if ($this->_knownIssues !== null) $XML .= "<knownIssues>".recode_string("utf8..xml",$this->_knownIssues)."</knownIssues>\n";
		if ($this->_repositoryURL !== null) $XML .= "<repositoryURL>".recode_string("utf8..xml",$this->_repositoryURL)."</repositoryURL>\n";
		if ($this->_releaseXML !== null) $XML .= "<releaseXML>".$this->_releaseXML."</releaseXML>\n";
		if ($this->_qualityCriteriaVerificationReport !== null) $XML .= "<qualityCriteriaVerificationReport>".recode_string("utf8..xml",$this->_qualityCriteriaVerificationReport)."</qualityCriteriaVerificationReport>\n";
		if ($this->_stageRolloutReport !== null) $XML .= "<stageRolloutReport>".recode_string("utf8..xml",$this->_stageRolloutReport)."</stageRolloutReport>\n";
		if ($this->_additionalDetails !== null) $XML .= "<additionalDetails>".recode_string("utf8..xml",$this->_additionalDetails)."</additionalDetails>\n";
		if ($this->_deleted !== null) $XML .= "<deleted>".$this->_deleted."</deleted>\n";
		if ($this->_extraFld1 !== null) $XML .= "<extraFld1>".recode_string("utf8..xml",$this->_extraFld1)."</extraFld1>\n";
		if ($this->_extraFld2 !== null) $XML .= "<extraFld2>".recode_string("utf8..xml",$this->_extraFld2)."</extraFld2>\n";
		if ($this->_extraFld3 !== null) $XML .= "<extraFld3>".recode_string("utf8..xml",$this->_extraFld3)."</extraFld3>\n";
		if ($this->_extraFld4 !== null) $XML .= "<extraFld4>".recode_string("utf8..xml",$this->_extraFld4)."</extraFld4>\n";
		if ($this->_extraFld5 !== null) $XML .= "<extraFld5>".recode_string("utf8..xml",$this->_extraFld5)."</extraFld5>\n";
		if ($this->_timestampInserted !== null) $XML .= "<timestampInserted>".recode_string("utf8..xml",$this->_timestampInserted)."</timestampInserted>\n";
		if ($this->_timestampLastUpdated !== null) $XML .= "<timestampLastUpdated>".recode_string("utf8..xml",$this->_timestampLastUpdated)."</timestampLastUpdated>\n";
		if ($this->_timestampLastStateChange !== null) $XML .= "<timestampLastStateChange>".recode_string("utf8..xml",$this->_timestampLastStateChange)."</timestampLastStateChange>\n";
		if ($this->_timestampReleaseDate !== null) $XML .= "<timestampReleaseDate>".recode_string("utf8..xml",$this->_timestampReleaseDate)."</timestampReleaseDate>\n";
		if ($this->_timestampLastProductionBuild !== null) $XML .= "<timestampLastProductionBuild>".recode_string("utf8..xml",$this->_timestampLastProductionBuild)."</timestampLastProductionBuild>\n";
		if ($this->_insertedBy !== null) $XML .= "<insertedBy>".$this->_insertedBy."</insertedBy>\n";
		$XML .= "</MetaProductRelease>\n";
		return $XML;
	}
}

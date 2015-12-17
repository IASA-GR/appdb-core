<?php
class Repository_Model_MetaPoaReleaseBase
{
	protected $_mapper;
	protected $_id;
	protected $_productReleaseId;
	protected $_productRelease;
	protected $_displayVersion;
	protected $_releaseNotes;
	protected $_changeLog;
	protected $_poaUrl;
	protected $_poaCandidateUrl;
	protected $_poaPath;
	protected $_targetPlatformCombId;
	protected $_targetCombonation;
	protected $_dMethodCombId;
	protected $_deployMethod;
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
	protected $_insertedBy;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid MetaPoaRelease property: '$name'");
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
			throw new Exception("Invalid MetaPoaRelease property: '$name'");
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

	public function setProductReleaseId($value)
	{
		/* if ( $value === null ) {
			$this->_productReleaseId = 'NULL';
		} else */ $this->_productReleaseId = $value;
		return $this;
	}

	public function getProductReleaseId()
	{
		return $this->_productReleaseId;
	}

	public function getProductRelease()
	{
		if ( $this->_productRelease === null ) {
			$MetaProductReleases = new Repository_Model_MetaProductReleases();
			$MetaProductReleases->filter->id->equals($this->getProductReleaseId());
			if ($MetaProductReleases->count() > 0) $this->_productRelease = $MetaProductReleases->items[0];
		}
		return $this->_productRelease;
	}

	public function setProductRelease($value)
	{
		if ( $value === null ) {
			$this->setProductReleaseId(null);
		} else {
			$this->setProductReleaseId($value->getId());
		}
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

	public function setPoaUrl($value)
	{
		/* if ( $value === null ) {
			$this->_poaUrl = 'NULL';
		} else */ $this->_poaUrl = $value;
		return $this;
	}

	public function getPoaUrl()
	{
		return $this->_poaUrl;
	}

	public function setPoaCandidateUrl($value)
	{
		/* if ( $value === null ) {
			$this->_poaCandidateUrl = 'NULL';
		} else */ $this->_poaCandidateUrl = $value;
		return $this;
	}

	public function getPoaCandidateUrl()
	{
		return $this->_poaCandidateUrl;
	}

	public function setPoaPath($value)
	{
		/* if ( $value === null ) {
			$this->_poaPath = 'NULL';
		} else */ $this->_poaPath = $value;
		return $this;
	}

	public function getPoaPath()
	{
		return $this->_poaPath;
	}

	public function setTargetPlatformCombId($value)
	{
		/* if ( $value === null ) {
			$this->_targetPlatformCombId = 'NULL';
		} else */ $this->_targetPlatformCombId = $value;
		return $this;
	}

	public function getTargetPlatformCombId()
	{
		return $this->_targetPlatformCombId;
	}

	public function getTargetCombonation()
	{
		if ( $this->_targetCombonation === null ) {
			$CommRepoAllowedPlatformCombinations = new Repository_Model_CommRepoAllowedPlatformCombinations();
			$CommRepoAllowedPlatformCombinations->filter->id->equals($this->getTargetPlatformCombId());
			if ($CommRepoAllowedPlatformCombinations->count() > 0) $this->_targetCombonation = $CommRepoAllowedPlatformCombinations->items[0];
		}
		return $this->_targetCombonation;
	}

	public function setTargetCombonation($value)
	{
		if ( $value === null ) {
			$this->setTargetPlatformCombId(null);
		} else {
			$this->setTargetPlatformCombId($value->getId());
		}
	}


	public function setDMethodCombId($value)
	{
		/* if ( $value === null ) {
			$this->_dMethodCombId = 'NULL';
		} else */ $this->_dMethodCombId = $value;
		return $this;
	}

	public function getDMethodCombId()
	{
		return $this->_dMethodCombId;
	}

	public function getDeployMethod()
	{
		if ( $this->_deployMethod === null ) {
			$CommRepoAllowedOsDmethodCombinations = new Repository_Model_CommRepoAllowedOsDmethodCombinations();
			$CommRepoAllowedOsDmethodCombinations->filter->id->equals($this->getDMethodCombId());
			if ($CommRepoAllowedOsDmethodCombinations->count() > 0) $this->_deployMethod = $CommRepoAllowedOsDmethodCombinations->items[0];
		}
		return $this->_deployMethod;
	}

	public function setDeployMethod($value)
	{
		if ( $value === null ) {
			$this->setDMethodCombId(null);
		} else {
			$this->setDMethodCombId($value->getId());
		}
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
			$this->setMapper(new Repository_Model_MetaPoaReleasesMapper());
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
		$XML = "<MetaPoaRelease>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_productReleaseId !== null) $XML .= "<productReleaseId>".$this->_productReleaseId."</productReleaseId>\n";
		if ( $recursive ) if ( $this->_productRelease === null ) $this->getProductRelease();
		if ( ! ($this->_productRelease === null) ) $XML .= $this->_productRelease->toXML();
		if ($this->_displayVersion !== null) $XML .= "<displayVersion>".recode_string("utf8..xml",$this->_displayVersion)."</displayVersion>\n";
		if ($this->_releaseNotes !== null) $XML .= "<releaseNotes>".recode_string("utf8..xml",$this->_releaseNotes)."</releaseNotes>\n";
		if ($this->_changeLog !== null) $XML .= "<changeLog>".recode_string("utf8..xml",$this->_changeLog)."</changeLog>\n";
		if ($this->_poaUrl !== null) $XML .= "<poaUrl>".recode_string("utf8..xml",$this->_poaUrl)."</poaUrl>\n";
		if ($this->_poaCandidateUrl !== null) $XML .= "<poaCandidateUrl>".recode_string("utf8..xml",$this->_poaCandidateUrl)."</poaCandidateUrl>\n";
		if ($this->_poaPath !== null) $XML .= "<poaPath>".recode_string("utf8..xml",$this->_poaPath)."</poaPath>\n";
		if ($this->_targetPlatformCombId !== null) $XML .= "<targetPlatformCombId>".$this->_targetPlatformCombId."</targetPlatformCombId>\n";
		if ( $recursive ) if ( $this->_targetCombonation === null ) $this->getTargetCombonation();
		if ( ! ($this->_targetCombonation === null) ) $XML .= $this->_targetCombonation->toXML();
		if ($this->_dMethodCombId !== null) $XML .= "<dMethodCombId>".$this->_dMethodCombId."</dMethodCombId>\n";
		if ( $recursive ) if ( $this->_deployMethod === null ) $this->getDeployMethod();
		if ( ! ($this->_deployMethod === null) ) $XML .= $this->_deployMethod->toXML();
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
		if ($this->_insertedBy !== null) $XML .= "<insertedBy>".$this->_insertedBy."</insertedBy>\n";
		$XML .= "</MetaPoaRelease>\n";
		return $XML;
	}
}

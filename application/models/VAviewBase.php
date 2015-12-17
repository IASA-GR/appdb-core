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
class Default_Model_VAviewBase
{
	protected $_mapper;
	protected $_vapplistID;
	protected $_vapplist;
	protected $_vappversionID;
	protected $_vappversion;
	protected $_vmiinstanceID;
	protected $_vmiinstance;
	protected $_size;
	protected $_uri;
	protected $_vmiinstanceVersion;
	protected $_checksum;
	protected $_checksumfunc;
	protected $_vmiinstanceNotes;
	protected $_vmiinstanceGUID;
	protected $_vmiinstanceAddedon;
	protected $_vmiinstanceAddedByID;
	protected $_vmiinstanceAddedBy;
	protected $_vmiflavourID;
	protected $_vmiflavour;
	protected $_autointegrity;
	protected $_coreminimum;
	protected $_ramminimum;
	protected $_vmiinstanceLastupdatedByID;
	protected $_vmiinstanceLastupdatedBy;
	protected $_vmiinstanceLastupdatedon;
	protected $_vmiinstanceDescription;
	protected $_vmiinstanceTitle;
	protected $_integrityStatus;
	protected $_integrityMessage;
	protected $_ramrecommend;
	protected $_corerecommend;
	protected $_accessinfo;
	protected $_vmiinstanceEnabled;
	protected $_initialsize;
	protected $_initialchecksum;
	protected $_ovfurl;
	protected $_vmiID;
	protected $_vmi;
	protected $_hypervisors;
	protected $_archID;
	protected $_arch;
	protected $_osID;
	protected $_os;
	protected $_osversion;
	protected $_format;
	protected $_vmiName;
	protected $_vmiDescription;
	protected $_vmiGUID;
	protected $_vaId;
	protected $_va;
	protected $_vmiNotes;
	protected $_groupname;
	protected $_vaName;
	protected $_appID;
	protected $_app;
	protected $_vaGUID;
	protected $_imglstPrivate;
	protected $_vaVersion;
	protected $_vaVersionGUID;
	protected $_vaVersionNotes;
	protected $_vaVersionPublished;
	protected $_vaVersionCreatedon;
	protected $_vaVersionExpireson;
	protected $_vaVersionEnabled;
	protected $_vaVersionArchived;
	protected $_vaVersionStatus;
	protected $_vaVersionArchivedon;
	protected $_submissionID;
	protected $_submission;
	protected $_vaVersionIsexternal;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VAview property: '$name'");
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
			throw new Exception("Invalid VAview property: '$name'");
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

	public function setVapplistID($value)
	{
		/* if ( $value === null ) {
			$this->_vapplistID = 'NULL';
		} else */ $this->_vapplistID = $value;
		return $this;
	}

	public function getVapplistID()
	{
		return $this->_vapplistID;
	}

	public function getVapplist()
	{
		if ( $this->_vapplist === null ) {
			$VALists = new Default_Model_VALists();
			$VALists->filter->id->equals($this->getVapplistID());
			if ($VALists->count() > 0) $this->_vapplist = $VALists->items[0];
		}
		return $this->_vapplist;
	}

	public function setVapplist($value)
	{
		if ( $value === null ) {
			$this->setVapplistID(null);
		} else {
			$this->setVapplistID($value->getId());
		}
	}


	public function setVappversionID($value)
	{
		/* if ( $value === null ) {
			$this->_vappversionID = 'NULL';
		} else */ $this->_vappversionID = $value;
		return $this;
	}

	public function getVappversionID()
	{
		return $this->_vappversionID;
	}

	public function getVappversion()
	{
		if ( $this->_vappversion === null ) {
			$VAversions = new Default_Model_VAversions();
			$VAversions->filter->id->equals($this->getVappversionID());
			if ($VAversions->count() > 0) $this->_vappversion = $VAversions->items[0];
		}
		return $this->_vappversion;
	}

	public function setVappversion($value)
	{
		if ( $value === null ) {
			$this->setVappversionID(null);
		} else {
			$this->setVappversionID($value->getId());
		}
	}


	public function setVmiinstanceID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceID = 'NULL';
		} else */ $this->_vmiinstanceID = $value;
		return $this;
	}

	public function getVmiinstanceID()
	{
		return $this->_vmiinstanceID;
	}

	public function getVmiinstance()
	{
		if ( $this->_vmiinstance === null ) {
			$VMIinstances = new Default_Model_VMIinstances();
			$VMIinstances->filter->id->equals($this->getVmiinstanceID());
			if ($VMIinstances->count() > 0) $this->_vmiinstance = $VMIinstances->items[0];
		}
		return $this->_vmiinstance;
	}

	public function setVmiinstance($value)
	{
		if ( $value === null ) {
			$this->setVmiinstanceID(null);
		} else {
			$this->setVmiinstanceID($value->getId());
		}
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

	public function setVmiinstanceVersion($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceVersion = 'NULL';
		} else */ $this->_vmiinstanceVersion = $value;
		return $this;
	}

	public function getVmiinstanceVersion()
	{
		return $this->_vmiinstanceVersion;
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

	public function setChecksumfunc($value)
	{
		/* if ( $value === null ) {
			$this->_checksumfunc = 'NULL';
		} else */ $this->_checksumfunc = $value;
		return $this;
	}

	public function getChecksumfunc()
	{
		return $this->_checksumfunc;
	}

	public function setVmiinstanceNotes($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceNotes = 'NULL';
		} else */ $this->_vmiinstanceNotes = $value;
		return $this;
	}

	public function getVmiinstanceNotes()
	{
		return $this->_vmiinstanceNotes;
	}

	public function setVmiinstanceGUID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceGUID = 'NULL';
		} else */ $this->_vmiinstanceGUID = $value;
		return $this;
	}

	public function getVmiinstanceGUID()
	{
		return $this->_vmiinstanceGUID;
	}

	public function setVmiinstanceAddedon($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceAddedon = 'NULL';
		} else */ $this->_vmiinstanceAddedon = $value;
		return $this;
	}

	public function getVmiinstanceAddedon()
	{
		return $this->_vmiinstanceAddedon;
	}

	public function setVmiinstanceAddedByID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceAddedByID = 'NULL';
		} else */ $this->_vmiinstanceAddedByID = $value;
		return $this;
	}

	public function getVmiinstanceAddedByID()
	{
		return $this->_vmiinstanceAddedByID;
	}

	public function getVmiinstanceAddedBy()
	{
		if ( $this->_vmiinstanceAddedBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getVmiinstanceAddedByID());
			if ($Researchers->count() > 0) $this->_vmiinstanceAddedBy = $Researchers->items[0];
		}
		return $this->_vmiinstanceAddedBy;
	}

	public function setVmiinstanceAddedBy($value)
	{
		if ( $value === null ) {
			$this->setVmiinstanceAddedByID(null);
		} else {
			$this->setVmiinstanceAddedByID($value->getId());
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

	public function getVmiflavour()
	{
		if ( $this->_vmiflavour === null ) {
			$VMIflavours = new Default_Model_VMIflavours();
			$VMIflavours->filter->id->equals($this->getVmiflavourID());
			if ($VMIflavours->count() > 0) $this->_vmiflavour = $VMIflavours->items[0];
		}
		return $this->_vmiflavour;
	}

	public function setVmiflavour($value)
	{
		if ( $value === null ) {
			$this->setVmiflavourID(null);
		} else {
			$this->setVmiflavourID($value->getId());
		}
	}


	public function setAutointegrity($value)
	{
		/* if ( $value === null ) {
			$this->_autointegrity = 'NULL';
		} else */ $this->_autointegrity = $value;
		return $this;
	}

	public function getAutointegrity()
	{
		$v = $this->_autointegrity;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setCoreminimum($value)
	{
		/* if ( $value === null ) {
			$this->_coreminimum = 'NULL';
		} else */ $this->_coreminimum = $value;
		return $this;
	}

	public function getCoreminimum()
	{
		return $this->_coreminimum;
	}

	public function setRamminimum($value)
	{
		/* if ( $value === null ) {
			$this->_ramminimum = 'NULL';
		} else */ $this->_ramminimum = $value;
		return $this;
	}

	public function getRamminimum()
	{
		return $this->_ramminimum;
	}

	public function setVmiinstanceLastupdatedByID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceLastupdatedByID = 'NULL';
		} else */ $this->_vmiinstanceLastupdatedByID = $value;
		return $this;
	}

	public function getVmiinstanceLastupdatedByID()
	{
		return $this->_vmiinstanceLastupdatedByID;
	}

	public function getVmiinstanceLastupdatedBy()
	{
		if ( $this->_vmiinstanceLastupdatedBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getVmiinstanceLastupdatedByID());
			if ($Researchers->count() > 0) $this->_vmiinstanceLastupdatedBy = $Researchers->items[0];
		}
		return $this->_vmiinstanceLastupdatedBy;
	}

	public function setVmiinstanceLastupdatedBy($value)
	{
		if ( $value === null ) {
			$this->setVmiinstanceLastupdatedByID(null);
		} else {
			$this->setVmiinstanceLastupdatedByID($value->getId());
		}
	}


	public function setVmiinstanceLastupdatedon($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceLastupdatedon = 'NULL';
		} else */ $this->_vmiinstanceLastupdatedon = $value;
		return $this;
	}

	public function getVmiinstanceLastupdatedon()
	{
		return $this->_vmiinstanceLastupdatedon;
	}

	public function setVmiinstanceDescription($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceDescription = 'NULL';
		} else */ $this->_vmiinstanceDescription = $value;
		return $this;
	}

	public function getVmiinstanceDescription()
	{
		return $this->_vmiinstanceDescription;
	}

	public function setVmiinstanceTitle($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceTitle = 'NULL';
		} else */ $this->_vmiinstanceTitle = $value;
		return $this;
	}

	public function getVmiinstanceTitle()
	{
		return $this->_vmiinstanceTitle;
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

	public function setRamrecommend($value)
	{
		/* if ( $value === null ) {
			$this->_ramrecommend = 'NULL';
		} else */ $this->_ramrecommend = $value;
		return $this;
	}

	public function getRamrecommend()
	{
		return $this->_ramrecommend;
	}

	public function setCorerecommend($value)
	{
		/* if ( $value === null ) {
			$this->_corerecommend = 'NULL';
		} else */ $this->_corerecommend = $value;
		return $this;
	}

	public function getCorerecommend()
	{
		return $this->_corerecommend;
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

	public function setVmiinstanceEnabled($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceEnabled = 'NULL';
		} else */ $this->_vmiinstanceEnabled = $value;
		return $this;
	}

	public function getVmiinstanceEnabled()
	{
		$v = $this->_vmiinstanceEnabled;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
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

	public function setOvfurl($value)
	{
		/* if ( $value === null ) {
			$this->_ovfurl = 'NULL';
		} else */ $this->_ovfurl = $value;
		return $this;
	}

	public function getOvfurl()
	{
		return $this->_ovfurl;
	}

	public function setVmiID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiID = 'NULL';
		} else */ $this->_vmiID = $value;
		return $this;
	}

	public function getVmiID()
	{
		return $this->_vmiID;
	}

	public function getVmi()
	{
		if ( $this->_vmi === null ) {
			$VMIs = new Default_Model_VMIs();
			$VMIs->filter->id->equals($this->getVmiID());
			if ($VMIs->count() > 0) $this->_vmi = $VMIs->items[0];
		}
		return $this->_vmi;
	}

	public function setVmi($value)
	{
		if ( $value === null ) {
			$this->setVmiID(null);
		} else {
			$this->setVmiID($value->getId());
		}
	}


	public function setHypervisors($value)
	{
		/* if ( $value === null ) {
			$this->_hypervisors = 'NULL';
		} else */ $this->_hypervisors = $value;
		return $this;
	}

	public function getHypervisors()
	{
		return $this->_hypervisors;
	}

	public function setArchID($value)
	{
		/* if ( $value === null ) {
			$this->_archID = 'NULL';
		} else */ $this->_archID = $value;
		return $this;
	}

	public function getArchID()
	{
		return $this->_archID;
	}

	public function getArch()
	{
		if ( $this->_arch === null ) {
			$Archs = new Default_Model_Archs();
			$Archs->filter->id->equals($this->getArchID());
			if ($Archs->count() > 0) $this->_arch = $Archs->items[0];
		}
		return $this->_arch;
	}

	public function setArch($value)
	{
		if ( $value === null ) {
			$this->setArchID(null);
		} else {
			$this->setArchID($value->getId());
		}
	}


	public function setOsID($value)
	{
		/* if ( $value === null ) {
			$this->_osID = 'NULL';
		} else */ $this->_osID = $value;
		return $this;
	}

	public function getOsID()
	{
		return $this->_osID;
	}

	public function getOs()
	{
		if ( $this->_os === null ) {
			$OSes = new Default_Model_OSes();
			$OSes->filter->id->equals($this->getOsID());
			if ($OSes->count() > 0) $this->_os = $OSes->items[0];
		}
		return $this->_os;
	}

	public function setOs($value)
	{
		if ( $value === null ) {
			$this->setOsID(null);
		} else {
			$this->setOsID($value->getId());
		}
	}


	public function setOsversion($value)
	{
		/* if ( $value === null ) {
			$this->_osversion = 'NULL';
		} else */ $this->_osversion = $value;
		return $this;
	}

	public function getOsversion()
	{
		return $this->_osversion;
	}

	public function setFormat($value)
	{
		/* if ( $value === null ) {
			$this->_format = 'NULL';
		} else */ $this->_format = $value;
		return $this;
	}

	public function getFormat()
	{
		return $this->_format;
	}

	public function setVmiName($value)
	{
		/* if ( $value === null ) {
			$this->_vmiName = 'NULL';
		} else */ $this->_vmiName = $value;
		return $this;
	}

	public function getVmiName()
	{
		return $this->_vmiName;
	}

	public function setVmiDescription($value)
	{
		/* if ( $value === null ) {
			$this->_vmiDescription = 'NULL';
		} else */ $this->_vmiDescription = $value;
		return $this;
	}

	public function getVmiDescription()
	{
		return $this->_vmiDescription;
	}

	public function setVmiGUID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiGUID = 'NULL';
		} else */ $this->_vmiGUID = $value;
		return $this;
	}

	public function getVmiGUID()
	{
		return $this->_vmiGUID;
	}

	public function setVaId($value)
	{
		/* if ( $value === null ) {
			$this->_vaId = 'NULL';
		} else */ $this->_vaId = $value;
		return $this;
	}

	public function getVaId()
	{
		return $this->_vaId;
	}

	public function getVa()
	{
		if ( $this->_va === null ) {
			$VAs = new Default_Model_VAs();
			$VAs->filter->id->equals($this->getVaId());
			if ($VAs->count() > 0) $this->_va = $VAs->items[0];
		}
		return $this->_va;
	}

	public function setVa($value)
	{
		if ( $value === null ) {
			$this->setVaId(null);
		} else {
			$this->setVaId($value->getId());
		}
	}


	public function setVmiNotes($value)
	{
		/* if ( $value === null ) {
			$this->_vmiNotes = 'NULL';
		} else */ $this->_vmiNotes = $value;
		return $this;
	}

	public function getVmiNotes()
	{
		return $this->_vmiNotes;
	}

	public function setGroupname($value)
	{
		/* if ( $value === null ) {
			$this->_groupname = 'NULL';
		} else */ $this->_groupname = $value;
		return $this;
	}

	public function getGroupname()
	{
		return $this->_groupname;
	}

	public function setVaName($value)
	{
		/* if ( $value === null ) {
			$this->_vaName = 'NULL';
		} else */ $this->_vaName = $value;
		return $this;
	}

	public function getVaName()
	{
		return $this->_vaName;
	}

	public function setAppID($value)
	{
		/* if ( $value === null ) {
			$this->_appID = 'NULL';
		} else */ $this->_appID = $value;
		return $this;
	}

	public function getAppID()
	{
		return $this->_appID;
	}

	public function getApp()
	{
		if ( $this->_app === null ) {
			$Applications = new Default_Model_Applications();
			$Applications->filter->id->equals($this->getAppID());
			if ($Applications->count() > 0) $this->_app = $Applications->items[0];
		}
		return $this->_app;
	}

	public function setApp($value)
	{
		if ( $value === null ) {
			$this->setAppID(null);
		} else {
			$this->setAppID($value->getId());
		}
	}


	public function setVaGUID($value)
	{
		/* if ( $value === null ) {
			$this->_vaGUID = 'NULL';
		} else */ $this->_vaGUID = $value;
		return $this;
	}

	public function getVaGUID()
	{
		return $this->_vaGUID;
	}

	public function setImglstPrivate($value)
	{
		/* if ( $value === null ) {
			$this->_imglstPrivate = 'NULL';
		} else */ $this->_imglstPrivate = $value;
		return $this;
	}

	public function getImglstPrivate()
	{
		$v = $this->_imglstPrivate;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setVaVersion($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersion = 'NULL';
		} else */ $this->_vaVersion = $value;
		return $this;
	}

	public function getVaVersion()
	{
		return $this->_vaVersion;
	}

	public function setVaVersionGUID($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersionGUID = 'NULL';
		} else */ $this->_vaVersionGUID = $value;
		return $this;
	}

	public function getVaVersionGUID()
	{
		return $this->_vaVersionGUID;
	}

	public function setVaVersionNotes($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersionNotes = 'NULL';
		} else */ $this->_vaVersionNotes = $value;
		return $this;
	}

	public function getVaVersionNotes()
	{
		return $this->_vaVersionNotes;
	}

	public function setVaVersionPublished($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersionPublished = 'NULL';
		} else */ $this->_vaVersionPublished = $value;
		return $this;
	}

	public function getVaVersionPublished()
	{
		$v = $this->_vaVersionPublished;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setVaVersionCreatedon($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersionCreatedon = 'NULL';
		} else */ $this->_vaVersionCreatedon = $value;
		return $this;
	}

	public function getVaVersionCreatedon()
	{
		return $this->_vaVersionCreatedon;
	}

	public function setVaVersionExpireson($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersionExpireson = 'NULL';
		} else */ $this->_vaVersionExpireson = $value;
		return $this;
	}

	public function getVaVersionExpireson()
	{
		return $this->_vaVersionExpireson;
	}

	public function setVaVersionEnabled($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersionEnabled = 'NULL';
		} else */ $this->_vaVersionEnabled = $value;
		return $this;
	}

	public function getVaVersionEnabled()
	{
		$v = $this->_vaVersionEnabled;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setVaVersionArchived($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersionArchived = 'NULL';
		} else */ $this->_vaVersionArchived = $value;
		return $this;
	}

	public function getVaVersionArchived()
	{
		$v = $this->_vaVersionArchived;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setVaVersionStatus($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersionStatus = 'NULL';
		} else */ $this->_vaVersionStatus = $value;
		return $this;
	}

	public function getVaVersionStatus()
	{
		return $this->_vaVersionStatus;
	}

	public function setVaVersionArchivedon($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersionArchivedon = 'NULL';
		} else */ $this->_vaVersionArchivedon = $value;
		return $this;
	}

	public function getVaVersionArchivedon()
	{
		return $this->_vaVersionArchivedon;
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

	public function getSubmission()
	{
		if ( $this->_submission === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getSubmissionID());
			if ($Researchers->count() > 0) $this->_submission = $Researchers->items[0];
		}
		return $this->_submission;
	}

	public function setSubmission($value)
	{
		if ( $value === null ) {
			$this->setSubmissionID(null);
		} else {
			$this->setSubmissionID($value->getId());
		}
	}


	public function setVaVersionIsexternal($value)
	{
		/* if ( $value === null ) {
			$this->_vaVersionIsexternal = 'NULL';
		} else */ $this->_vaVersionIsexternal = $value;
		return $this;
	}

	public function getVaVersionIsexternal()
	{
		$v = $this->_vaVersionIsexternal;
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
			$this->setMapper(new Default_Model_VAviewsMapper());
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
		$XML = "<VAview>\n";
		if ($this->_vapplistID !== null) $XML .= "<vapplistID>".$this->_vapplistID."</vapplistID>\n";
		if ( $recursive ) if ( $this->_vapplist === null ) $this->getVapplist();
		if ( ! ($this->_vapplist === null) ) $XML .= $this->_vapplist->toXML();
		if ($this->_vappversionID !== null) $XML .= "<vappversionID>".$this->_vappversionID."</vappversionID>\n";
		if ( $recursive ) if ( $this->_vappversion === null ) $this->getVappversion();
		if ( ! ($this->_vappversion === null) ) $XML .= $this->_vappversion->toXML();
		if ($this->_vmiinstanceID !== null) $XML .= "<vmiinstanceID>".$this->_vmiinstanceID."</vmiinstanceID>\n";
		if ( $recursive ) if ( $this->_vmiinstance === null ) $this->getVmiinstance();
		if ( ! ($this->_vmiinstance === null) ) $XML .= $this->_vmiinstance->toXML();
		if ($this->_size !== null) $XML .= "<size>".recode_string("utf8..xml",$this->_size)."</size>\n";
		if ($this->_uri !== null) $XML .= "<uri>".recode_string("utf8..xml",$this->_uri)."</uri>\n";
		if ($this->_vmiinstanceVersion !== null) $XML .= "<vmiinstanceVersion>".recode_string("utf8..xml",$this->_vmiinstanceVersion)."</vmiinstanceVersion>\n";
		if ($this->_checksum !== null) $XML .= "<checksum>".recode_string("utf8..xml",$this->_checksum)."</checksum>\n";
		if ($this->_checksumfunc !== null) $XML .= "<checksumfunc>".$this->_checksumfunc."</checksumfunc>\n";
		if ($this->_vmiinstanceNotes !== null) $XML .= "<vmiinstanceNotes>".recode_string("utf8..xml",$this->_vmiinstanceNotes)."</vmiinstanceNotes>\n";
		if ($this->_vmiinstanceGUID !== null) $XML .= "<vmiinstanceGUID>".$this->_vmiinstanceGUID."</vmiinstanceGUID>\n";
		if ($this->_vmiinstanceAddedon !== null) $XML .= "<vmiinstanceAddedon>".recode_string("utf8..xml",$this->_vmiinstanceAddedon)."</vmiinstanceAddedon>\n";
		if ($this->_vmiinstanceAddedByID !== null) $XML .= "<vmiinstanceAddedByID>".$this->_vmiinstanceAddedByID."</vmiinstanceAddedByID>\n";
		if ( $recursive ) if ( $this->_vmiinstanceAddedBy === null ) $this->getVmiinstanceAddedBy();
		if ( ! ($this->_vmiinstanceAddedBy === null) ) $XML .= $this->_vmiinstanceAddedBy->toXML();
		if ($this->_vmiflavourID !== null) $XML .= "<vmiflavourID>".$this->_vmiflavourID."</vmiflavourID>\n";
		if ( $recursive ) if ( $this->_vmiflavour === null ) $this->getVmiflavour();
		if ( ! ($this->_vmiflavour === null) ) $XML .= $this->_vmiflavour->toXML();
		if ($this->_autointegrity !== null) $XML .= "<autointegrity>".$this->_autointegrity."</autointegrity>\n";
		if ($this->_coreminimum !== null) $XML .= "<coreminimum>".$this->_coreminimum."</coreminimum>\n";
		if ($this->_ramminimum !== null) $XML .= "<ramminimum>".recode_string("utf8..xml",$this->_ramminimum)."</ramminimum>\n";
		if ($this->_vmiinstanceLastupdatedByID !== null) $XML .= "<vmiinstanceLastupdatedByID>".$this->_vmiinstanceLastupdatedByID."</vmiinstanceLastupdatedByID>\n";
		if ( $recursive ) if ( $this->_vmiinstanceLastupdatedBy === null ) $this->getVmiinstanceLastupdatedBy();
		if ( ! ($this->_vmiinstanceLastupdatedBy === null) ) $XML .= $this->_vmiinstanceLastupdatedBy->toXML();
		if ($this->_vmiinstanceLastupdatedon !== null) $XML .= "<vmiinstanceLastupdatedon>".recode_string("utf8..xml",$this->_vmiinstanceLastupdatedon)."</vmiinstanceLastupdatedon>\n";
		if ($this->_vmiinstanceDescription !== null) $XML .= "<vmiinstanceDescription>".recode_string("utf8..xml",$this->_vmiinstanceDescription)."</vmiinstanceDescription>\n";
		if ($this->_vmiinstanceTitle !== null) $XML .= "<vmiinstanceTitle>".recode_string("utf8..xml",$this->_vmiinstanceTitle)."</vmiinstanceTitle>\n";
		if ($this->_integrityStatus !== null) $XML .= "<integrityStatus>".recode_string("utf8..xml",$this->_integrityStatus)."</integrityStatus>\n";
		if ($this->_integrityMessage !== null) $XML .= "<integrityMessage>".recode_string("utf8..xml",$this->_integrityMessage)."</integrityMessage>\n";
		if ($this->_ramrecommend !== null) $XML .= "<ramrecommend>".$this->_ramrecommend."</ramrecommend>\n";
		if ($this->_corerecommend !== null) $XML .= "<corerecommend>".$this->_corerecommend."</corerecommend>\n";
		if ($this->_accessinfo !== null) $XML .= "<accessinfo>".recode_string("utf8..xml",$this->_accessinfo)."</accessinfo>\n";
		if ($this->_vmiinstanceEnabled !== null) $XML .= "<vmiinstanceEnabled>".$this->_vmiinstanceEnabled."</vmiinstanceEnabled>\n";
		if ($this->_initialsize !== null) $XML .= "<initialsize>".$this->_initialsize."</initialsize>\n";
		if ($this->_initialchecksum !== null) $XML .= "<initialchecksum>".recode_string("utf8..xml",$this->_initialchecksum)."</initialchecksum>\n";
		if ($this->_ovfurl !== null) $XML .= "<ovfurl>".recode_string("utf8..xml",$this->_ovfurl)."</ovfurl>\n";
		if ($this->_vmiID !== null) $XML .= "<vmiID>".$this->_vmiID."</vmiID>\n";
		if ( $recursive ) if ( $this->_vmi === null ) $this->getVmi();
		if ( ! ($this->_vmi === null) ) $XML .= $this->_vmi->toXML();
		if ($this->_hypervisors !== null) $XML .= "<hypervisors>".$this->_hypervisors."</hypervisors>\n";
		if ($this->_archID !== null) $XML .= "<archID>".$this->_archID."</archID>\n";
		if ( $recursive ) if ( $this->_arch === null ) $this->getArch();
		if ( ! ($this->_arch === null) ) $XML .= $this->_arch->toXML();
		if ($this->_osID !== null) $XML .= "<osID>".$this->_osID."</osID>\n";
		if ( $recursive ) if ( $this->_os === null ) $this->getOs();
		if ( ! ($this->_os === null) ) $XML .= $this->_os->toXML();
		if ($this->_osversion !== null) $XML .= "<osversion>".recode_string("utf8..xml",$this->_osversion)."</osversion>\n";
		if ($this->_format !== null) $XML .= "<format>".recode_string("utf8..xml",$this->_format)."</format>\n";
		if ($this->_vmiName !== null) $XML .= "<vmiName>".recode_string("utf8..xml",$this->_vmiName)."</vmiName>\n";
		if ($this->_vmiDescription !== null) $XML .= "<vmiDescription>".recode_string("utf8..xml",$this->_vmiDescription)."</vmiDescription>\n";
		if ($this->_vmiGUID !== null) $XML .= "<vmiGUID>".recode_string("utf8..xml",$this->_vmiGUID)."</vmiGUID>\n";
		if ($this->_vaId !== null) $XML .= "<vaId>".$this->_vaId."</vaId>\n";
		if ( $recursive ) if ( $this->_va === null ) $this->getVa();
		if ( ! ($this->_va === null) ) $XML .= $this->_va->toXML();
		if ($this->_vmiNotes !== null) $XML .= "<vmiNotes>".recode_string("utf8..xml",$this->_vmiNotes)."</vmiNotes>\n";
		if ($this->_groupname !== null) $XML .= "<groupname>".recode_string("utf8..xml",$this->_groupname)."</groupname>\n";
		if ($this->_vaName !== null) $XML .= "<vaName>".recode_string("utf8..xml",$this->_vaName)."</vaName>\n";
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ( $recursive ) if ( $this->_app === null ) $this->getApp();
		if ( ! ($this->_app === null) ) $XML .= $this->_app->toXML();
		if ($this->_vaGUID !== null) $XML .= "<vaGUID>".recode_string("utf8..xml",$this->_vaGUID)."</vaGUID>\n";
		if ($this->_imglstPrivate !== null) $XML .= "<imglstPrivate>".$this->_imglstPrivate."</imglstPrivate>\n";
		if ($this->_vaVersion !== null) $XML .= "<vaVersion>".recode_string("utf8..xml",$this->_vaVersion)."</vaVersion>\n";
		if ($this->_vaVersionGUID !== null) $XML .= "<vaVersionGUID>".$this->_vaVersionGUID."</vaVersionGUID>\n";
		if ($this->_vaVersionNotes !== null) $XML .= "<vaVersionNotes>".recode_string("utf8..xml",$this->_vaVersionNotes)."</vaVersionNotes>\n";
		if ($this->_vaVersionPublished !== null) $XML .= "<vaVersionPublished>".$this->_vaVersionPublished."</vaVersionPublished>\n";
		if ($this->_vaVersionCreatedon !== null) $XML .= "<vaVersionCreatedon>".recode_string("utf8..xml",$this->_vaVersionCreatedon)."</vaVersionCreatedon>\n";
		if ($this->_vaVersionExpireson !== null) $XML .= "<vaVersionExpireson>".recode_string("utf8..xml",$this->_vaVersionExpireson)."</vaVersionExpireson>\n";
		if ($this->_vaVersionEnabled !== null) $XML .= "<vaVersionEnabled>".$this->_vaVersionEnabled."</vaVersionEnabled>\n";
		if ($this->_vaVersionArchived !== null) $XML .= "<vaVersionArchived>".$this->_vaVersionArchived."</vaVersionArchived>\n";
		if ($this->_vaVersionStatus !== null) $XML .= "<vaVersionStatus>".recode_string("utf8..xml",$this->_vaVersionStatus)."</vaVersionStatus>\n";
		if ($this->_vaVersionArchivedon !== null) $XML .= "<vaVersionArchivedon>".recode_string("utf8..xml",$this->_vaVersionArchivedon)."</vaVersionArchivedon>\n";
		if ($this->_submissionID !== null) $XML .= "<submissionID>".$this->_submissionID."</submissionID>\n";
		if ( $recursive ) if ( $this->_submission === null ) $this->getSubmission();
		if ( ! ($this->_submission === null) ) $XML .= $this->_submission->toXML();
		if ($this->_vaVersionIsexternal !== null) $XML .= "<vaVersionIsexternal>".$this->_vaVersionIsexternal."</vaVersionIsexternal>\n";
		$XML .= "</VAview>\n";
		return $XML;
	}
}

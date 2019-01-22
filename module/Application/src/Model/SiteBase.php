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
class Default_Model_SiteBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_shortname;
	protected $_officialname;
	protected $_description;
	protected $_portalurl;
	protected $_homeurl;
	protected $_contactemail;
	protected $_contacttel;
	protected $_alarmemail;
	protected $_csirtemail;
	protected $_giisurl;
	protected $_countryID;
	protected $_country;
	protected $_countrycode;
	protected $_countryname;
	protected $_regionID;
	protected $_region;
	protected $_regionname;
	protected $_tier;
	protected $_subgrID;
	protected $_roc;
	protected $_productioninfrastructure;
	protected $_certificationstatus;
	protected $_timezone;
	protected $_latitude;
	protected $_longitude;
	protected $_domainname;
	protected $_ip;
	protected $_guID;
	protected $_datasource;
	protected $_createdon;
	protected $_createdby;
	protected $_updatedon;
	protected $_updatedby;
	protected $_deleted;
	protected $_deletedon;
	protected $_deletedby;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Site property: '$name'");
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
			throw new Exception("Invalid Site property: '$name'");
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

	public function setShortname($value)
	{
		/* if ( $value === null ) {
			$this->_shortname = 'NULL';
		} else */ $this->_shortname = $value;
		return $this;
	}

	public function getShortname()
	{
		return $this->_shortname;
	}

	public function setOfficialname($value)
	{
		/* if ( $value === null ) {
			$this->_officialname = 'NULL';
		} else */ $this->_officialname = $value;
		return $this;
	}

	public function getOfficialname()
	{
		return $this->_officialname;
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

	public function setPortalurl($value)
	{
		/* if ( $value === null ) {
			$this->_portalurl = 'NULL';
		} else */ $this->_portalurl = $value;
		return $this;
	}

	public function getPortalurl()
	{
		return $this->_portalurl;
	}

	public function setHomeurl($value)
	{
		/* if ( $value === null ) {
			$this->_homeurl = 'NULL';
		} else */ $this->_homeurl = $value;
		return $this;
	}

	public function getHomeurl()
	{
		return $this->_homeurl;
	}

	public function setContactemail($value)
	{
		/* if ( $value === null ) {
			$this->_contactemail = 'NULL';
		} else */ $this->_contactemail = $value;
		return $this;
	}

	public function getContactemail()
	{
		return $this->_contactemail;
	}

	public function setContacttel($value)
	{
		/* if ( $value === null ) {
			$this->_contacttel = 'NULL';
		} else */ $this->_contacttel = $value;
		return $this;
	}

	public function getContacttel()
	{
		return $this->_contacttel;
	}

	public function setAlarmemail($value)
	{
		/* if ( $value === null ) {
			$this->_alarmemail = 'NULL';
		} else */ $this->_alarmemail = $value;
		return $this;
	}

	public function getAlarmemail()
	{
		return $this->_alarmemail;
	}

	public function setCsirtemail($value)
	{
		/* if ( $value === null ) {
			$this->_csirtemail = 'NULL';
		} else */ $this->_csirtemail = $value;
		return $this;
	}

	public function getCsirtemail()
	{
		return $this->_csirtemail;
	}

	public function setGiisurl($value)
	{
		/* if ( $value === null ) {
			$this->_giisurl = 'NULL';
		} else */ $this->_giisurl = $value;
		return $this;
	}

	public function getGiisurl()
	{
		return $this->_giisurl;
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


	public function setCountrycode($value)
	{
		/* if ( $value === null ) {
			$this->_countrycode = 'NULL';
		} else */ $this->_countrycode = $value;
		return $this;
	}

	public function getCountrycode()
	{
		return $this->_countrycode;
	}

	public function setCountryname($value)
	{
		/* if ( $value === null ) {
			$this->_countryname = 'NULL';
		} else */ $this->_countryname = $value;
		return $this;
	}

	public function getCountryname()
	{
		return $this->_countryname;
	}

	public function setRegionID($value)
	{
		/* if ( $value === null ) {
			$this->_regionID = 'NULL';
		} else */ $this->_regionID = $value;
		return $this;
	}

	public function getRegionID()
	{
		return $this->_regionID;
	}

	public function getRegion()
	{
		if ( $this->_region === null ) {
			$Regions = new Default_Model_Regions();
			$Regions->filter->id->equals($this->getRegionID());
			if ($Regions->count() > 0) $this->_region = $Regions->items[0];
		}
		return $this->_region;
	}

	public function setRegion($value)
	{
		if ( $value === null ) {
			$this->setRegionID(null);
		} else {
			$this->setRegionID($value->getId());
		}
	}


	public function setRegionname($value)
	{
		/* if ( $value === null ) {
			$this->_regionname = 'NULL';
		} else */ $this->_regionname = $value;
		return $this;
	}

	public function getRegionname()
	{
		return $this->_regionname;
	}

	public function setTier($value)
	{
		/* if ( $value === null ) {
			$this->_tier = 'NULL';
		} else */ $this->_tier = $value;
		return $this;
	}

	public function getTier()
	{
		return $this->_tier;
	}

	public function setSubgrID($value)
	{
		/* if ( $value === null ) {
			$this->_subgrID = 'NULL';
		} else */ $this->_subgrID = $value;
		return $this;
	}

	public function getSubgrID()
	{
		return $this->_subgrID;
	}

	public function setRoc($value)
	{
		/* if ( $value === null ) {
			$this->_roc = 'NULL';
		} else */ $this->_roc = $value;
		return $this;
	}

	public function getRoc()
	{
		return $this->_roc;
	}

	public function setProductioninfrastructure($value)
	{
		/* if ( $value === null ) {
			$this->_productioninfrastructure = 'NULL';
		} else */ $this->_productioninfrastructure = $value;
		return $this;
	}

	public function getProductioninfrastructure()
	{
		return $this->_productioninfrastructure;
	}

	public function setCertificationstatus($value)
	{
		/* if ( $value === null ) {
			$this->_certificationstatus = 'NULL';
		} else */ $this->_certificationstatus = $value;
		return $this;
	}

	public function getCertificationstatus()
	{
		return $this->_certificationstatus;
	}

	public function setTimezone($value)
	{
		/* if ( $value === null ) {
			$this->_timezone = 'NULL';
		} else */ $this->_timezone = $value;
		return $this;
	}

	public function getTimezone()
	{
		return $this->_timezone;
	}

	public function setLatitude($value)
	{
		/* if ( $value === null ) {
			$this->_latitude = 'NULL';
		} else */ $this->_latitude = $value;
		return $this;
	}

	public function getLatitude()
	{
		return $this->_latitude;
	}

	public function setLongitude($value)
	{
		/* if ( $value === null ) {
			$this->_longitude = 'NULL';
		} else */ $this->_longitude = $value;
		return $this;
	}

	public function getLongitude()
	{
		return $this->_longitude;
	}

	public function setDomainname($value)
	{
		/* if ( $value === null ) {
			$this->_domainname = 'NULL';
		} else */ $this->_domainname = $value;
		return $this;
	}

	public function getDomainname()
	{
		return $this->_domainname;
	}

	public function setIp($value)
	{
		/* if ( $value === null ) {
			$this->_ip = 'NULL';
		} else */ $this->_ip = $value;
		return $this;
	}

	public function getIp()
	{
		return $this->_ip;
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

	public function setDatasource($value)
	{
		/* if ( $value === null ) {
			$this->_datasource = 'NULL';
		} else */ $this->_datasource = $value;
		return $this;
	}

	public function getDatasource()
	{
		return $this->_datasource;
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

	public function setCreatedby($value)
	{
		/* if ( $value === null ) {
			$this->_createdby = 'NULL';
		} else */ $this->_createdby = $value;
		return $this;
	}

	public function getCreatedby()
	{
		return $this->_createdby;
	}

	public function setUpdatedon($value)
	{
		/* if ( $value === null ) {
			$this->_updatedon = 'NULL';
		} else */ $this->_updatedon = $value;
		return $this;
	}

	public function getUpdatedon()
	{
		return $this->_updatedon;
	}

	public function setUpdatedby($value)
	{
		/* if ( $value === null ) {
			$this->_updatedby = 'NULL';
		} else */ $this->_updatedby = $value;
		return $this;
	}

	public function getUpdatedby()
	{
		return $this->_updatedby;
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

	public function setDeletedby($value)
	{
		/* if ( $value === null ) {
			$this->_deletedby = 'NULL';
		} else */ $this->_deletedby = $value;
		return $this;
	}

	public function getDeletedby()
	{
		return $this->_deletedby;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_SitesMapper());
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
		$XML = "<Site>\n";
		if ($this->_id !== null) $XML .= "<id>".recode_string("utf8..xml",$this->_id)."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_shortname !== null) $XML .= "<shortname>".recode_string("utf8..xml",$this->_shortname)."</shortname>\n";
		if ($this->_officialname !== null) $XML .= "<officialname>".recode_string("utf8..xml",$this->_officialname)."</officialname>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_portalurl !== null) $XML .= "<portalurl>".recode_string("utf8..xml",$this->_portalurl)."</portalurl>\n";
		if ($this->_homeurl !== null) $XML .= "<homeurl>".recode_string("utf8..xml",$this->_homeurl)."</homeurl>\n";
		if ($this->_contactemail !== null) $XML .= "<contactemail>".recode_string("utf8..xml",$this->_contactemail)."</contactemail>\n";
		if ($this->_contacttel !== null) $XML .= "<contacttel>".recode_string("utf8..xml",$this->_contacttel)."</contacttel>\n";
		if ($this->_alarmemail !== null) $XML .= "<alarmemail>".recode_string("utf8..xml",$this->_alarmemail)."</alarmemail>\n";
		if ($this->_csirtemail !== null) $XML .= "<csirtemail>".recode_string("utf8..xml",$this->_csirtemail)."</csirtemail>\n";
		if ($this->_giisurl !== null) $XML .= "<giisurl>".recode_string("utf8..xml",$this->_giisurl)."</giisurl>\n";
		if ($this->_countryID !== null) $XML .= "<countryID>".$this->_countryID."</countryID>\n";
		if ( $recursive ) if ( $this->_country === null ) $this->getCountry();
		if ( ! ($this->_country === null) ) $XML .= $this->_country->toXML();
		if ($this->_countrycode !== null) $XML .= "<countrycode>".recode_string("utf8..xml",$this->_countrycode)."</countrycode>\n";
		if ($this->_countryname !== null) $XML .= "<countryname>".recode_string("utf8..xml",$this->_countryname)."</countryname>\n";
		if ($this->_regionID !== null) $XML .= "<regionID>".$this->_regionID."</regionID>\n";
		if ( $recursive ) if ( $this->_region === null ) $this->getRegion();
		if ( ! ($this->_region === null) ) $XML .= $this->_region->toXML();
		if ($this->_regionname !== null) $XML .= "<regionname>".recode_string("utf8..xml",$this->_regionname)."</regionname>\n";
		if ($this->_tier !== null) $XML .= "<tier>".recode_string("utf8..xml",$this->_tier)."</tier>\n";
		if ($this->_subgrID !== null) $XML .= "<subgrID>".recode_string("utf8..xml",$this->_subgrID)."</subgrID>\n";
		if ($this->_roc !== null) $XML .= "<roc>".recode_string("utf8..xml",$this->_roc)."</roc>\n";
		if ($this->_productioninfrastructure !== null) $XML .= "<productioninfrastructure>".recode_string("utf8..xml",$this->_productioninfrastructure)."</productioninfrastructure>\n";
		if ($this->_certificationstatus !== null) $XML .= "<certificationstatus>".recode_string("utf8..xml",$this->_certificationstatus)."</certificationstatus>\n";
		if ($this->_timezone !== null) $XML .= "<timezone>".recode_string("utf8..xml",$this->_timezone)."</timezone>\n";
		if ($this->_latitude !== null) $XML .= "<latitude>".recode_string("utf8..xml",$this->_latitude)."</latitude>\n";
		if ($this->_longitude !== null) $XML .= "<longitude>".recode_string("utf8..xml",$this->_longitude)."</longitude>\n";
		if ($this->_domainname !== null) $XML .= "<domainname>".recode_string("utf8..xml",$this->_domainname)."</domainname>\n";
		if ($this->_ip !== null) $XML .= "<ip>".recode_string("utf8..xml",$this->_ip)."</ip>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		if ($this->_datasource !== null) $XML .= "<datasource>".recode_string("utf8..xml",$this->_datasource)."</datasource>\n";
		if ($this->_createdon !== null) $XML .= "<createdon>".recode_string("utf8..xml",$this->_createdon)."</createdon>\n";
		if ($this->_createdby !== null) $XML .= "<createdby>".recode_string("utf8..xml",$this->_createdby)."</createdby>\n";
		if ($this->_updatedon !== null) $XML .= "<updatedon>".recode_string("utf8..xml",$this->_updatedon)."</updatedon>\n";
		if ($this->_updatedby !== null) $XML .= "<updatedby>".recode_string("utf8..xml",$this->_updatedby)."</updatedby>\n";
		if ($this->_deleted !== null) $XML .= "<deleted>".$this->_deleted."</deleted>\n";
		if ($this->_deletedon !== null) $XML .= "<deletedon>".recode_string("utf8..xml",$this->_deletedon)."</deletedon>\n";
		if ($this->_deletedby !== null) $XML .= "<deletedby>".recode_string("utf8..xml",$this->_deletedby)."</deletedby>\n";
		$XML .= "</Site>\n";
		return $XML;
	}
}

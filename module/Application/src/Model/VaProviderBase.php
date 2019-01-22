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
class Default_Model_VaProviderBase
{
	protected $_mapper;
	protected $_id;
	protected $_sitename;
	protected $_url;
	protected $_gocdbUrl;
	protected $_hostname;
	protected $_hostDn;
	protected $_hostIp;
	protected $_hostOsId;
	protected $_hostArchId;
	protected $_beta;
	protected $_inProduction;
	protected $_nodeMonitored;
	protected $_countryId;
	protected $_ngi;
	protected $_guID;
	protected $_serviceid;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VaProvider property: '$name'");
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
			throw new Exception("Invalid VaProvider property: '$name'");
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

	public function setSitename($value)
	{
		/* if ( $value === null ) {
			$this->_sitename = 'NULL';
		} else */ $this->_sitename = $value;
		return $this;
	}

	public function getSitename()
	{
		return $this->_sitename;
	}

	public function setUrl($value)
	{
		/* if ( $value === null ) {
			$this->_url = 'NULL';
		} else */ $this->_url = $value;
		return $this;
	}

	public function getUrl()
	{
		return $this->_url;
	}

	public function setGocdbUrl($value)
	{
		/* if ( $value === null ) {
			$this->_gocdbUrl = 'NULL';
		} else */ $this->_gocdbUrl = $value;
		return $this;
	}

	public function getGocdbUrl()
	{
		return $this->_gocdbUrl;
	}

	public function setServiceID($value)
	{
		/* if ( $value === null ) {
			$this->_serviceid = 'NULL';
		} else */ $this->_serviceid = $value;
		return $this;
	}

	public function getServiceID()
	{
		return $this->_serviceid;
	}

	public function setHostname($value)
	{
		/* if ( $value === null ) {
			$this->_hostname = 'NULL';
		} else */ $this->_hostname = $value;
		return $this;
	}

	public function getHostname()
	{
		return $this->_hostname;
	}

	public function setHostDn($value)
	{
		/* if ( $value === null ) {
			$this->_hostDn = 'NULL';
		} else */ $this->_hostDn = $value;
		return $this;
	}

	public function getHostDn()
	{
		return $this->_hostDn;
	}

	public function setHostIp($value)
	{
		/* if ( $value === null ) {
			$this->_hostIp = 'NULL';
		} else */ $this->_hostIp = $value;
		return $this;
	}

	public function getHostIp()
	{
		return $this->_hostIp;
	}

	public function setHostOsId($value)
	{
		/* if ( $value === null ) {
			$this->_hostOsId = 'NULL';
		} else */ $this->_hostOsId = $value;
		return $this;
	}

	public function getHostOsId()
	{
		return $this->_hostOsId;
	}

	public function setHostArchId($value)
	{
		/* if ( $value === null ) {
			$this->_hostArchId = 'NULL';
		} else */ $this->_hostArchId = $value;
		return $this;
	}

	public function getHostArchId()
	{
		return $this->_hostArchId;
	}

	public function setBeta($value)
	{
		/* if ( $value === null ) {
			$this->_beta = 'NULL';
		} else */ $this->_beta = $value;
		return $this;
	}

	public function getBeta()
	{
		$v = $this->_beta;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setInProduction($value)
	{
		/* if ( $value === null ) {
			$this->_inProduction = 'NULL';
		} else */ $this->_inProduction = $value;
		return $this;
	}

	public function getInProduction()
	{
		$v = $this->_inProduction;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setNodeMonitored($value)
	{
		/* if ( $value === null ) {
			$this->_nodeMonitored = 'NULL';
		} else */ $this->_nodeMonitored = $value;
		return $this;
	}

	public function getNodeMonitored()
	{
		$v = $this->_nodeMonitored;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setCountryId($value)
	{
		/* if ( $value === null ) {
			$this->_countryId = 'NULL';
		} else */ $this->_countryId = $value;
		return $this;
	}

	public function getCountryId()
	{
		return $this->_countryId;
	}

	public function setNgi($value)
	{
		/* if ( $value === null ) {
			$this->_ngi = 'NULL';
		} else */ $this->_ngi = $value;
		return $this;
	}

	public function getNgi()
	{
		return $this->_ngi;
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

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_VaProvidersMapper());
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
		$XML = "<VaProvider>\n";
		if ($this->_id !== null) $XML .= "<id>".recode_string("utf8..xml",$this->_id)."</id>\n";
		if ($this->_sitename !== null) $XML .= "<sitename>".recode_string("utf8..xml",$this->_sitename)."</sitename>\n";
		if ($this->_url !== null) $XML .= "<url>".recode_string("utf8..xml",$this->_url)."</url>\n";
		if ($this->_gocdbUrl !== null) $XML .= "<gocdbUrl>".recode_string("utf8..xml",$this->_gocdbUrl)."</gocdbUrl>\n";
		if ($this->_serviceid !== null) $XML .= "<serviceid>".recode_string("utf8..xml",$this->_serviceid)."</serviceid>\n";
		if ($this->_hostname !== null) $XML .= "<hostname>".recode_string("utf8..xml",$this->_hostname)."</hostname>\n";
		if ($this->_hostDn !== null) $XML .= "<hostDn>".recode_string("utf8..xml",$this->_hostDn)."</hostDn>\n";
		if ($this->_hostIp !== null) $XML .= "<hostIp>".recode_string("utf8..xml",$this->_hostIp)."</hostIp>\n";
		if ($this->_hostOsId !== null) $XML .= "<hostOsId>".$this->_hostOsId."</hostOsId>\n";
		if ($this->_hostArchId !== null) $XML .= "<hostArchId>".$this->_hostArchId."</hostArchId>\n";
		if ($this->_beta !== null) $XML .= "<beta>".$this->_beta."</beta>\n";
		if ($this->_inProduction !== null) $XML .= "<inProduction>".$this->_inProduction."</inProduction>\n";
		if ($this->_nodeMonitored !== null) $XML .= "<nodeMonitored>".$this->_nodeMonitored."</nodeMonitored>\n";
		if ($this->_countryId !== null) $XML .= "<countryId>".$this->_countryId."</countryId>\n";
		if ($this->_ngi !== null) $XML .= "<ngi>".recode_string("utf8..xml",$this->_ngi)."</ngi>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		$XML .= "</VaProvider>\n";
		return $XML;
	}
}

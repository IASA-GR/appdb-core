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
class Default_Model_VOBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_scope;
	protected $_validated;
	protected $_description;
	protected $_homepage;
	protected $_enrollment;
	protected $_aup;
	protected $_domainID;
	protected $_domain;
	protected $_deleted;
	protected $_deletedon;
	protected $_alias;
	protected $_status;
	protected $_guid;
	protected $_sourceid;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VO property: '$name'");
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
			throw new Exception("Invalid VO property: '$name'");
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

	public function setGUID($value)
	{
		/* if ( $value === null ) {
			$this->_id = 'NULL';
		} else */ $this->_guid = $value;
		return $this;
	}

	public function getGUID()
	{
		return $this->_guid;
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

	public function setScope($value)
	{
		/* if ( $value === null ) {
			$this->_scope = 'NULL';
		} else */ $this->_scope = $value;
		return $this;
	}

	public function getScope()
	{
		return $this->_scope;
	}

	public function setValidated($value)
	{
		/* if ( $value === null ) {
			$this->_validated = 'NULL';
		} else */ $this->_validated = $value;
		return $this;
	}

	public function getValidated()
	{
		return $this->_validated;
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

	public function setHomepage($value)
	{
		/* if ( $value === null ) {
			$this->_homepage = 'NULL';
		} else */ $this->_homepage = $value;
		return $this;
	}

	public function getHomepage()
	{
		return $this->_homepage;
	}

	public function setEnrollment($value)
	{
		/* if ( $value === null ) {
			$this->_enrollment = 'NULL';
		} else */ $this->_enrollment = $value;
		return $this;
	}

	public function getEnrollment()
	{
		return $this->_enrollment;
	}

	public function setAup($value)
	{
		/* if ( $value === null ) {
			$this->_aup = 'NULL';
		} else */ $this->_aup = $value;
		return $this;
	}

	public function getAup()
	{
		return $this->_aup;
	}

	public function setDomainID($value)
	{
		/* if ( $value === null ) {
			$this->_domainID = 'NULL';
		} else */ $this->_domainID = $value;
		return $this;
	}

	public function getDomainID()
	{
		return $this->_domainID;
	}

	public function getDomain()
	{
		if ( $this->_domain === null ) {
			$Domains = new Default_Model_Domains();
			$Domains->filter->id->equals($this->getDomainID());
			if ($Domains->count() > 0) $this->_domain = $Domains->items[0];
		}
		return $this->_domain;
	}

	public function setDomain($value)
	{
		if ( $value === null ) {
			$this->setDomainID(null);
		} else {
			$this->setDomainID($value->getId());
		}
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

	public function setAlias($value)
	{
		/* if ( $value === null ) {
			$this->_alias = 'NULL';
		} else */ $this->_alias = $value;
		return $this;
	}

	public function getAlias()
	{
		return $this->_alias;
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

	public function setSourceID($value)
	{
		/* if ( $value === null ) {
			$this->_sourceid = 'NULL';
		} else */ $this->_sourceid = $value;
		return $this;
	}

	public function getSourceID()
	{
		return $this->_sourceid;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_VOsMapper());
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
		$XML = "<VO>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_guid !== null) $XML .= "<id>".$this->_guid."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_scope !== null) $XML .= "<scope>".recode_string("utf8..xml",$this->_scope)."</scope>\n";
		if ($this->_validated !== null) $XML .= "<validated>".recode_string("utf8..xml",$this->_validated)."</validated>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_homepage !== null) $XML .= "<homepage>".recode_string("utf8..xml",$this->_homepage)."</homepage>\n";
		if ($this->_enrollment !== null) $XML .= "<enrollment>".recode_string("utf8..xml",$this->_enrollment)."</enrollment>\n";
		if ($this->_aup !== null) $XML .= "<aup>".recode_string("utf8..xml",$this->_aup)."</aup>\n";
		if ($this->_domainID !== null) $XML .= "<domainID>".$this->_domainID."</domainID>\n";
		if ( $recursive ) if ( $this->_domain === null ) $this->getDomain();
		if ( ! ($this->_domain === null) ) $XML .= $this->_domain->toXML();
		if ($this->_deleted !== null) $XML .= "<deleted>".$this->_deleted."</deleted>\n";
		if ($this->_deletedon !== null) $XML .= "<deletedon>".recode_string("utf8..xml",$this->_deletedon)."</deletedon>\n";
		if ($this->_alias !== null) $XML .= "<alias>".recode_string("utf8..xml",$this->_alias)."</alias>\n";
		if ($this->_status !== null) $XML .= "<status>".recode_string("utf8..xml",$this->_status)."</status>\n";
		if ($this->_sourceid !== null) $XML .= "<sourceid>".$this->_sourceid."</sourceid>\n";
		$XML .= "</VO>\n";
		return $XML;
	}
}

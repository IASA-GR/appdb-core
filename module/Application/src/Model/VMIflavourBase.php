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
class Default_Model_VMIflavourBase
{
	protected $_mapper;
	protected $_id;
	protected $_vmiID;
	protected $_vmi;
	protected $_hypervisors;
	protected $_archID;
	protected $_arch;
	protected $_osID;
	protected $_os;
	protected $_osVersion;
	protected $_format;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VMIflavour property: '$name'");
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
			throw new Exception("Invalid VMIflavour property: '$name'");
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


	public function setOsVersion($value)
	{
		/* if ( $value === null ) {
			$this->_osVersion = 'NULL';
		} else */ $this->_osVersion = $value;
		return $this;
	}

	public function getOsVersion()
	{
		return $this->_osVersion;
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

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_VMIflavoursMapper());
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
		$XML = "<VMIflavour>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_vmiID !== null) $XML .= "<vmiID>".$this->_vmiID."</vmiID>\n";
		if ( $recursive ) if ( $this->_vmi === null ) $this->getVmi();
		if ( ! ($this->_vmi === null) ) $XML .= $this->_vmi->toXML();
		if ($this->_hypervisors !== null) $XML .= "<hypervisors>".recode_string("utf8..xml",$this->_hypervisors)."</hypervisors>\n";
		if ($this->_archID !== null) $XML .= "<archID>".$this->_archID."</archID>\n";
		if ( $recursive ) if ( $this->_arch === null ) $this->getArch();
		if ( ! ($this->_arch === null) ) $XML .= $this->_arch->toXML();
		if ($this->_osID !== null) $XML .= "<osID>".$this->_osID."</osID>\n";
		if ( $recursive ) if ( $this->_os === null ) $this->getOs();
		if ( ! ($this->_os === null) ) $XML .= $this->_os->toXML();
		if ($this->_osVersion !== null) $XML .= "<osVersion>".recode_string("utf8..xml",$this->_osVersion)."</osVersion>\n";
		if ($this->_format !== null) $XML .= "<format>".recode_string("utf8..xml",$this->_format)."</format>\n";
		$XML .= "</VMIflavour>\n";
		return $XML;
	}
}

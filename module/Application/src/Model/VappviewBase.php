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
class Default_Model_VappviewBase
{
	protected $_mapper;
	protected $_vapplicationID;
	protected $_vapplication;
	protected $_vappversionID;
	protected $_vmiID;
	protected $_vmiinstanceID;
	protected $_vmiflavourID;
	protected $_vappversionguID;
	protected $_vmiguID;
	protected $_vmiinstanceguID;
	protected $_vapplicationname;
	protected $_vappversionversion;
	protected $_vmigroupname;
	protected $_instanceversion;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Vappview property: '$name'");
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
			throw new Exception("Invalid Vappview property: '$name'");
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

	public function setVapplicationID($value)
	{
		/* if ( $value === null ) {
			$this->_vapplicationID = 'NULL';
		} else */ $this->_vapplicationID = $value;
		return $this;
	}
	
	public function getVapplicationID()
	{
		return $this->_vapplicationID;
	}

	public function getVapplication()
	{
		if ( $this->_vapplication === null ) {
			$VAs = new Default_Model_VAs();
			$VAs->filter->id->equals($this->getVapplicationID());
			if ($VAs->count() > 0) $this->_vapplication = $VAs->items[0];
		}
		return $this->_vapplication;
	}

	public function setVapplication($value)
	{
		if ( $value === null ) {
			$this->setVapplicationID(null);
		} else {
			$this->setVapplicationID($value->getId());
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


	public function setVappversionguID($value)
	{
		/* if ( $value === null ) {
			$this->_vappversionguID = 'NULL';
		} else */ $this->_vappversionguID = $value;
		return $this;
	}

	public function getVappversionguID()
	{
		return $this->_vappversionguID;
	}

	public function setVmiguID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiguID = 'NULL';
		} else */ $this->_vmiguID = $value;
		return $this;
	}

	public function getVmiguID()
	{
		return $this->_vmiguID;
	}

	public function setVmiinstanceguID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceguID = 'NULL';
		} else */ $this->_vmiinstanceguID = $value;
		return $this;
	}

	public function getVmiinstanceguID()
	{
		return $this->_vmiinstanceguID;
	}

	public function setVapplicationname($value)
	{
		/* if ( $value === null ) {
			$this->_vapplicationname = 'NULL';
		} else */ $this->_vapplicationname = $value;
		return $this;
	}

	public function getVapplicationname()
	{
		return $this->_vapplicationname;
	}

	public function setVappversionversion($value)
	{
		/* if ( $value === null ) {
			$this->_vappversionversion = 'NULL';
		} else */ $this->_vappversionversion = $value;
		return $this;
	}

	public function getVappversionversion()
	{
		return $this->_vappversionversion;
	}

	public function setVmigroupname($value)
	{
		/* if ( $value === null ) {
			$this->_vmigroupname = 'NULL';
		} else */ $this->_vmigroupname = $value;
		return $this;
	}

	public function getVmigroupname()
	{
		return $this->_vmigroupname;
	}

	public function setInstanceversion($value)
	{
		/* if ( $value === null ) {
			$this->_instanceversion = 'NULL';
		} else */ $this->_instanceversion = $value;
		return $this;
	}

	public function getInstanceversion()
	{
		return $this->_instanceversion;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_VappviewsMapper());
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
		$XML = "<Vappview>\n";
		if ($this->_vapplicationID !== null) $XML .= "<vapplicationID>".$this->_vapplicationID."</vapplicationID>\n";
		if ( $recursive ) if ( $this->_vapplication === null ) $this->getVapplication();
		if ( ! ($this->_vapplication === null) ) $XML .= $this->_vapplication->toXML();
		if ($this->_vappversionID !== null) $XML .= "<vappversionID>".$this->_vappversionID."</vappversionID>\n";
		if ( $recursive ) if ( $this->_vappversion === null ) $this->getVappversion();
		if ( ! ($this->_vappversion === null) ) $XML .= $this->_vappversion->toXML();
		if ($this->_vmiID !== null) $XML .= "<vmiID>".$this->_vmiID."</vmiID>\n";
		if ( $recursive ) if ( $this->_vmi === null ) $this->getVmi();
		if ( ! ($this->_vmi === null) ) $XML .= $this->_vmi->toXML();
		if ($this->_vmiinstanceID !== null) $XML .= "<vmiinstanceID>".$this->_vmiinstanceID."</vmiinstanceID>\n";
		if ( $recursive ) if ( $this->_vmiinstance === null ) $this->getVmiinstance();
		if ( ! ($this->_vmiinstance === null) ) $XML .= $this->_vmiinstance->toXML();
		if ($this->_vmiflavourID !== null) $XML .= "<vmiflavourID>".$this->_vmiflavourID."</vmiflavourID>\n";
		if ( $recursive ) if ( $this->_vmiflavour === null ) $this->getVmiflavour();
		if ( ! ($this->_vmiflavour === null) ) $XML .= $this->_vmiflavour->toXML();
		if ($this->_vappversionguID !== null) $XML .= "<vappversionguID>".recode_string("utf8..xml",$this->_vappversionguID)."</vappversionguID>\n";
		if ($this->_vmiguID !== null) $XML .= "<vmiguID>".$this->_vmiguID."</vmiguID>\n";
		if ($this->_vmiinstanceguID !== null) $XML .= "<vmiinstanceguID>".$this->_vmiinstanceguID."</vmiinstanceguID>\n";
		if ($this->_vapplicationname !== null) $XML .= "<vapplicationname>".recode_string("utf8..xml",$this->_vapplicationname)."</vapplicationname>\n";
		if ($this->_vappversionversion !== null) $XML .= "<vappversionversion>".recode_string("utf8..xml",$this->_vappversionversion)."</vappversionversion>\n";
		if ($this->_vmigroupname !== null) $XML .= "<vmigroupname>".recode_string("utf8..xml",$this->_vmigroupname)."</vmigroupname>\n";
		if ($this->_instanceversion !== null) $XML .= "<instanceversion>".recode_string("utf8..xml",$this->_instanceversion)."</instanceversion>\n";
		$XML .= "</Vappview>\n";
		return $XML;
	}
}

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
class Default_Model_VAListBase
{
	protected $_mapper;
	protected $_id;
	protected $_vappversionID;
	protected $_VAversion;
	protected $_vmiinstanceID;
	protected $_VMIinstance;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VAList property: '$name'");
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
			throw new Exception("Invalid VAList property: '$name'");
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

	public function getVAversion()
	{
		if ( $this->_VAversion === null ) {
			$VAversions = new Default_Model_VAversions();
			$VAversions->filter->id->equals($this->getVappversionID());
			if ($VAversions->count() > 0) $this->_VAversion = $VAversions->items[0];
		}
		return $this->_VAversion;
	}

	public function setVAversion($value)
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

	public function getVMIinstance()
	{
		if ( $this->_VMIinstance === null ) {
			$VMIinstances = new Default_Model_VMIinstances();
			$VMIinstances->filter->id->equals($this->getVmiinstanceID());
			if ($VMIinstances->count() > 0) $this->_VMIinstance = $VMIinstances->items[0];
		}
		return $this->_VMIinstance;
	}

	public function setVMIinstance($value)
	{
		if ( $value === null ) {
			$this->setVmiinstanceID(null);
		} else {
			$this->setVmiinstanceID($value->getId());
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
			$this->setMapper(new Default_Model_VAListsMapper());
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
		$XML = "<VAList>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_vappversionID !== null) $XML .= "<vappversionID>".$this->_vappversionID."</vappversionID>\n";
		if ( $recursive ) if ( $this->_VAversion === null ) $this->getVAversion();
		if ( ! ($this->_VAversion === null) ) $XML .= $this->_VAversion->toXML();
		if ($this->_vmiinstanceID !== null) $XML .= "<vmiinstanceID>".$this->_vmiinstanceID."</vmiinstanceID>\n";
		if ( $recursive ) if ( $this->_VMIinstance === null ) $this->getVMIinstance();
		if ( ! ($this->_VMIinstance === null) ) $XML .= $this->_VMIinstance->toXML();
		$XML .= "</VAList>\n";
		return $XML;
	}
}

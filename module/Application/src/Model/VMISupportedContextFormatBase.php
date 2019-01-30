<?php
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and VMIinstances (http://www.iasa.gr)
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
namespace Application\Model;

class VMISupportedContextFormatBase
{
	protected $_mapper;
	protected $_fmtID;
	protected $_contextFormat;
	protected $_vmiinstanceID;
	protected $_vmiInstance;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VMISupportedContextFormat property: '$name'");
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
			throw new Exception("Invalid VMISupportedContextFormat property: '$name'");
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

	public function setFmtID($value)
	{
		/* if ( $value === null ) {
			$this->_fmtID = 'NULL';
		} else */ $this->_fmtID = $value;
		return $this;
	}

	public function getFmtID()
	{
		return $this->_fmtID;
	}

	public function getContextFormat()
	{
		if ( $this->_contextFormat === null ) {
			$cfs = new ContextFormats();
			$cfs->filter->id->equals($this->getFmtID());
			if ($cfs->count() > 0) $this->_contextFormat = $cfs->items[0];
		}
		return $this->_contextFormat;
	}

	public function setContextFormat($value)
	{
		if ( $value === null ) {
			$this->setFmtID(null);
		} else {
			$this->setFmtID($value->getId());
		}
	}


	public function setVMIinstanceID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiinstanceID = 'NULL';
		} else */ $this->_vmiinstanceID = $value;
		return $this;
	}

	public function getVMIinstanceID()
	{
		return $this->_vmiinstanceID;
	}

	public function getVMIinstance()
	{
		if ( $this->_vmiInstance === null ) {
			$VMIinstances = new VMIinstances();
			$VMIinstances->filter->id->equals($this->getVMIinstanceID());
			if ($VMIinstances->count() > 0) $this->_vmiInstance = $VMIinstances->items[0];
		}
		return $this->_vmiInstance;
	}

	public function setVMIinstance($value)
	{
		if ( $value === null ) {
			$this->setVMIinstanceID(null);
		} else {
			$this->setVMIinstanceID($value->getId());
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
			$this->setMapper(new VMISupportedContextFormatsMapper());
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
		$XML = "<VMISupportedContextFormat>\n";
		if ($this->_fmtID !== null) $XML .= "<fmtID>".$this->_fmtID."</fmtID>\n";
		if ( $recursive ) if ( $this->_contextFormat === null ) $this->getContextFormat();
		if ( ! ($this->_contextFormat === null) ) $XML .= $this->_contextFormat->toXML();
		if ($this->_vmiinstanceID !== null) $XML .= "<vmiinstanceID>".$this->_vmiinstanceID."</vmiinstanceID>\n";
		if ( $recursive ) if ( $this->_vmiInstance === null ) $this->getVMIinstance();
		if ( ! ($this->_vmiInstance === null) ) $XML .= $this->_vmiInstance->toXML();
		$XML .= "</VMISupportedContextFormat>\n";
		return $XML;
	}
}

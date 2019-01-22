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
class Default_Model_VMIinstanceContextScriptBase
{
	protected $_mapper;
	protected $_id;
	protected $_vmiinstanceID;
	protected $_VMIinstance;
	protected $_contextScriptID;
	protected $_contextScript;
	protected $_addedOn;
	protected $_addedByID;
	protected $_addedBy;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VMIinstanceContextScript property: '$name'");
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
			throw new Exception("Invalid VMIinstanceContextScript property: '$name'");
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


	public function setContextScriptID($value)
	{
		/* if ( $value === null ) {
			$this->_contextScriptID = 'NULL';
		} else */ $this->_contextScriptID = $value;
		return $this;
	}

	public function getContextScriptID()
	{
		return $this->_contextScriptID;
	}

	public function getContextScript()
	{
		if ( $this->_contextScript === null ) {
			$ContextScripts = new Default_Model_ContextScripts();
			$ContextScripts->filter->id->equals($this->getContextScriptID());
			if ($ContextScripts->count() > 0) $this->_contextScript = $ContextScripts->items[0];
		}
		return $this->_contextScript;
	}

	public function setContextScript($value)
	{
		if ( $value === null ) {
			$this->setContextScriptID(null);
		} else {
			$this->setContextScriptID($value->getId());
		}
	}


	public function setAddedOn($value)
	{
		/* if ( $value === null ) {
			$this->_addedOn = 'NULL';
		} else */ $this->_addedOn = $value;
		return $this;
	}

	public function getAddedOn()
	{
		return $this->_addedOn;
	}

	public function setAddedByID($value)
	{
		/* if ( $value === null ) {
			$this->_addedByID = 'NULL';
		} else */ $this->_addedByID = $value;
		return $this;
	}

	public function getAddedByID()
	{
		return $this->_addedByID;
	}

	public function getAddedBy()
	{
		if ( $this->_addedBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getAddedByID());
			if ($Researchers->count() > 0) $this->_addedBy = $Researchers->items[0];
		}
		return $this->_addedBy;
	}

	public function setAddedBy($value)
	{
		if ( $value === null ) {
			$this->setAddedByID(null);
		} else {
			$this->setAddedByID($value->getId());
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
			$this->setMapper(new Default_Model_VMIinstanceContextScriptsMapper());
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
		$XML = "<VMIinstanceContextScript>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_vmiinstanceID !== null) $XML .= "<vmiinstanceID>".$this->_vmiinstanceID."</vmiinstanceID>\n";
		if ( $recursive ) if ( $this->_VMIinstance === null ) $this->getVMIinstance();
		if ( ! ($this->_VMIinstance === null) ) $XML .= $this->_VMIinstance->toXML();
		if ($this->_contextScriptID !== null) $XML .= "<contextScriptID>".$this->_contextScriptID."</contextScriptID>\n";
		if ( $recursive ) if ( $this->_contextScript === null ) $this->getContextScript();
		if ( ! ($this->_contextScript === null) ) $XML .= $this->_contextScript->toXML();
		if ($this->_addedOn !== null) $XML .= "<addedOn>".recode_string("utf8..xml",$this->_addedOn)."</addedOn>\n";
		if ($this->_addedByID !== null) $XML .= "<addedByID>".$this->_addedByID."</addedByID>\n";
		if ( $recursive ) if ( $this->_addedBy === null ) $this->getAddedBy();
		if ( ! ($this->_addedBy === null) ) $XML .= $this->_addedBy->toXML();
		$XML .= "</VMIinstanceContextScript>\n";
		return $XML;
	}
}

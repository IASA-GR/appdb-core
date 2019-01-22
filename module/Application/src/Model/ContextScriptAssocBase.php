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
class Default_Model_ContextScriptAssocBase
{
	protected $_mapper;
	protected $_id;
	protected $_contextID;
	protected $_context;
	protected $_contextscriptID;
	protected $_contextscript;
	protected $_addedbyID;
	protected $_addedby;
	protected $_addedon;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid ContextScriptAssoc property: '$name'");
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
			throw new Exception("Invalid ContextScriptAssoc property: '$name'");
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

	public function setContextID($value)
	{
		/* if ( $value === null ) {
			$this->_contextID = 'NULL';
		} else */ $this->_contextID = $value;
		return $this;
	}

	public function getContextID()
	{
		return $this->_contextID;
	}

	public function getContext()
	{
		if ( $this->_context === null ) {
			$Contexts = new Default_Model_Contexts();
			$Contexts->filter->id->equals($this->getContextID());
			if ($Contexts->count() > 0) $this->_context = $Contexts->items[0];
		}
		return $this->_context;
	}

	public function setContext($value)
	{
		if ( $value === null ) {
			$this->setContextID(null);
		} else {
			$this->setContextID($value->getId());
		}
	}


	public function setContextscriptID($value)
	{
		/* if ( $value === null ) {
			$this->_contextscriptID = 'NULL';
		} else */ $this->_contextscriptID = $value;
		return $this;
	}

	public function getContextscriptID()
	{
		return $this->_contextscriptID;
	}

	public function getContextscript()
	{
		if ( $this->_contextscript === null ) {
			$ContextScripts = new Default_Model_ContextScripts();
			$ContextScripts->filter->id->equals($this->getContextscriptID());
			if ($ContextScripts->count() > 0) $this->_contextscript = $ContextScripts->items[0];
		}
		return $this->_contextscript;
	}

	public function setContextscript($value)
	{
		if ( $value === null ) {
			$this->setContextscriptID(null);
		} else {
			$this->setContextscriptID($value->getId());
		}
	}


	public function setAddedbyID($value)
	{
		/* if ( $value === null ) {
			$this->_addedbyID = 'NULL';
		} else */ $this->_addedbyID = $value;
		return $this;
	}

	public function getAddedbyID()
	{
		return $this->_addedbyID;
	}

	public function getAddedby()
	{
		if ( $this->_addedby === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getAddedbyID());
			if ($Researchers->count() > 0) $this->_addedby = $Researchers->items[0];
		}
		return $this->_addedby;
	}

	public function setAddedby($value)
	{
		if ( $value === null ) {
			$this->setAddedbyID(null);
		} else {
			$this->setAddedbyID($value->getId());
		}
	}


	public function setAddedon($value)
	{
		/* if ( $value === null ) {
			$this->_addedon = 'NULL';
		} else */ $this->_addedon = $value;
		return $this;
	}

	public function getAddedon()
	{
		return $this->_addedon;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_ContextScriptAssocsMapper());
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
		$XML = "<ContextScriptAssoc>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_contextID !== null) $XML .= "<contextID>".$this->_contextID."</contextID>\n";
		if ( $recursive ) if ( $this->_context === null ) $this->getContext();
		if ( ! ($this->_context === null) ) $XML .= $this->_context->toXML();
		if ($this->_contextscriptID !== null) $XML .= "<contextscriptID>".$this->_contextscriptID."</contextscriptID>\n";
		if ( $recursive ) if ( $this->_contextscript === null ) $this->getContextscript();
		if ( ! ($this->_contextscript === null) ) $XML .= $this->_contextscript->toXML();
		if ($this->_addedbyID !== null) $XML .= "<addedbyID>".$this->_addedbyID."</addedbyID>\n";
		if ( $recursive ) if ( $this->_addedby === null ) $this->getAddedby();
		if ( ! ($this->_addedby === null) ) $XML .= $this->_addedby->toXML();
		if ($this->_addedon !== null) $XML .= "<addedon>".recode_string("utf8..xml",$this->_addedon)."</addedon>\n";
		$XML .= "</ContextScriptAssoc>\n";
		return $XML;
	}
}

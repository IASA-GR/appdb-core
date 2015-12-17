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
class Default_Model_ContextBase
{
	protected $_mapper;
	protected $_id;
	protected $_applicationID;
	protected $_application;
	protected $_addedbyID;
	protected $_addedby;
	protected $_addedon;
	protected $_guID;
	protected $_lastupdatedbyID;
	protected $_lastupdatedby;
	protected $_lastupdatedon;
	protected $_version;
	protected $_description;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Context property: '$name'");
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
			throw new Exception("Invalid Context property: '$name'");
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

	public function setApplicationID($value)
	{
		/* if ( $value === null ) {
			$this->_applicationID = 'NULL';
		} else */ $this->_applicationID = $value;
		return $this;
	}

	public function getApplicationID()
	{
		return $this->_applicationID;
	}

	public function getApplication()
	{
		if ( $this->_application === null ) {
			$Applications = new Default_Model_Applications();
			$Applications->filter->id->equals($this->getApplicationID());
			if ($Applications->count() > 0) $this->_application = $Applications->items[0];
		}
		return $this->_application;
	}

	public function setApplication($value)
	{
		if ( $value === null ) {
			$this->setApplicationID(null);
		} else {
			$this->setApplicationID($value->getId());
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

	public function setLastupdatedbyID($value)
	{
		/* if ( $value === null ) {
			$this->_lastupdatedbyID = 'NULL';
		} else */ $this->_lastupdatedbyID = $value;
		return $this;
	}

	public function getLastupdatedbyID()
	{
		return $this->_lastupdatedbyID;
	}

	public function getLastupdatedby()
	{
		if ( $this->_lastupdatedby === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getLastupdatedbyID());
			if ($Researchers->count() > 0) $this->_lastupdatedby = $Researchers->items[0];
		}
		return $this->_lastupdatedby;
	}

	public function setLastupdatedby($value)
	{
		if ( $value === null ) {
			$this->setLastupdatedbyID(null);
		} else {
			$this->setLastupdatedbyID($value->getId());
		}
	}


	public function setLastupdatedon($value)
	{
		/* if ( $value === null ) {
			$this->_lastupdatedon = 'NULL';
		} else */ $this->_lastupdatedon = $value;
		return $this;
	}

	public function getLastupdatedon()
	{
		return $this->_lastupdatedon;
	}

	public function setVersion($value)
	{
		/* if ( $value === null ) {
			$this->_version = 'NULL';
		} else */ $this->_version = $value;
		return $this;
	}

	public function getVersion()
	{
		return $this->_version;
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

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_ContextsMapper());
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
		$XML = "<Context>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_applicationID !== null) $XML .= "<applicationID>".$this->_applicationID."</applicationID>\n";
		if ( $recursive ) if ( $this->_application === null ) $this->getApplication();
		if ( ! ($this->_application === null) ) $XML .= $this->_application->toXML();
		if ($this->_addedbyID !== null) $XML .= "<addedbyID>".$this->_addedbyID."</addedbyID>\n";
		if ( $recursive ) if ( $this->_addedby === null ) $this->getAddedby();
		if ( ! ($this->_addedby === null) ) $XML .= $this->_addedby->toXML();
		if ($this->_addedon !== null) $XML .= "<addedon>".recode_string("utf8..xml",$this->_addedon)."</addedon>\n";
		if ($this->_guID !== null) $XML .= "<guID>".recode_string("utf8..xml",$this->_guID)."</guID>\n";
		if ($this->_lastupdatedbyID !== null) $XML .= "<lastupdatedbyID>".$this->_lastupdatedbyID."</lastupdatedbyID>\n";
		if ( $recursive ) if ( $this->_lastupdatedby === null ) $this->getLastupdatedby();
		if ( ! ($this->_lastupdatedby === null) ) $XML .= $this->_lastupdatedby->toXML();
		if ($this->_lastupdatedon !== null) $XML .= "<lastupdatedon>".recode_string("utf8..xml",$this->_lastupdatedon)."</lastupdatedon>\n";
		if ($this->_version !== null) $XML .= "<version>".recode_string("utf8..xml",$this->_version)."</version>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		$XML .= "</Context>\n";
		return $XML;
	}
}

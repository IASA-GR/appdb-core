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
class Default_Model_AppModInfoBase
{
	protected $_mapper;
	protected $_id;
	protected $_appID;
	protected $_application;
	protected $_moddedBy;
	protected $_moderator;
	protected $_moddedOn;
	protected $_modReason;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AppModInfo property: '$name'");
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
			throw new Exception("Invalid AppModInfo property: '$name'");
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

	public function setAppID($value)
	{
		/* if ( $value === null ) {
			$this->_appID = 'NULL';
		} else */ $this->_appID = $value;
		return $this;
	}

	public function getAppID()
	{
		return $this->_appID;
	}

	public function getApplication()
	{
		if ( $this->_application === null ) {
			$Applications = new Default_Model_Applications();
			$Applications->filter->id->equals($this->getAppID());
			if ($Applications->count() > 0) $this->_application = $Applications->items[0];
		}
		return $this->_application;
	}

	public function setApplication($value)
	{
		if ( $value === null ) {
			$this->setAppID(null);
		} else {
			$this->setAppID($value->getId());
		}
	}


	public function setModdedBy($value)
	{
		/* if ( $value === null ) {
			$this->_moddedBy = 'NULL';
		} else */ $this->_moddedBy = $value;
		return $this;
	}

	public function getModdedBy()
	{
		return $this->_moddedBy;
	}

	public function getModerator()
	{
		if ( $this->_moderator === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getModdedBy());
			if ($Researchers->count() > 0) $this->_moderator = $Researchers->items[0];
		}
		return $this->_moderator;
	}

	public function setModerator($value)
	{
		if ( $value === null ) {
			$this->setModdedBy(null);
		} else {
			$this->setModdedBy($value->getId());
		}
	}


	public function setModdedOn($value)
	{
		/* if ( $value === null ) {
			$this->_moddedOn = 'NULL';
		} else */ $this->_moddedOn = $value;
		return $this;
	}

	public function getModdedOn()
	{
		return $this->_moddedOn;
	}

	public function setModReason($value)
	{
		/* if ( $value === null ) {
			$this->_modReason = 'NULL';
		} else */ $this->_modReason = $value;
		return $this;
	}

	public function getModReason()
	{
		return $this->_modReason;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_AppModInfosMapper());
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
		$XML = "<AppModInfo>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ( $recursive ) if ( $this->_application === null ) $this->getApplication();
		if ( ! ($this->_application === null) ) $XML .= $this->_application->toXML();
		if ($this->_moddedBy !== null) $XML .= "<moddedBy>".$this->_moddedBy."</moddedBy>\n";
		if ( $recursive ) if ( $this->_moderator === null ) $this->getModerator();
		if ( ! ($this->_moderator === null) ) $XML .= $this->_moderator->toXML();
		if ($this->_moddedOn !== null) $XML .= "<moddedOn>".recode_string("utf8..xml",$this->_moddedOn)."</moddedOn>\n";
		if ($this->_modReason !== null) $XML .= "<modReason>".recode_string("utf8..xml",$this->_modReason)."</modReason>\n";
		$XML .= "</AppModInfo>\n";
		return $XML;
	}
}

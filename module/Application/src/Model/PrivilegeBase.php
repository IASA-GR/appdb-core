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
class Default_Model_PrivilegeBase
{
	protected $_mapper;
	protected $_actionID;
	protected $_action;
	protected $_object;
	protected $_id;
	protected $_actor;
	protected $_revoked;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Privilege property: '$name'");
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
			throw new Exception("Invalid Privilege property: '$name'");
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

	public function setActionID($value)
	{
		/* if ( $value === null ) {
			$this->_actionID = 'NULL';
		} else */ $this->_actionID = $value;
		return $this;
	}

	public function getActionID()
	{
		return $this->_actionID;
	}

	public function getAction()
	{
		if ( $this->_action === null ) {
			$Actions = new Default_Model_Actions();
			$Actions->filter->id->equals($this->getActionID());
			if ($Actions->count() > 0) $this->_action = $Actions->items[0];
		}
		return $this->_action;
	}

	public function setAction($value)
	{
		if ( $value === null ) {
			$this->setActionID(null);
		} else {
			$this->setActionID($value->getId());
		}
	}


	public function setObject($value)
	{
		/* if ( $value === null ) {
			$this->_object = 'NULL';
		} else */ $this->_object = $value;
		return $this;
	}

	public function getObject()
	{
		return $this->_object;
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

	public function setActor($value)
	{
		/* if ( $value === null ) {
			$this->_actor = 'NULL';
		} else */ $this->_actor = $value;
		return $this;
	}

	public function getActor()
	{
		return $this->_actor;
	}

	public function setRevoked($value)
	{
		/* if ( $value === null ) {
			$this->_revoked = 'NULL';
		} else */ $this->_revoked = $value;
		return $this;
	}

	public function getRevoked()
	{
		$v = $this->_revoked;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
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
			$this->setMapper(new Default_Model_PrivilegesMapper());
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
		$XML = "<Privilege>\n";
		if ($this->_actionID !== null) $XML .= "<actionID>".$this->_actionID."</actionID>\n";
		if ( $recursive ) if ( $this->_action === null ) $this->getAction();
		if ( ! ($this->_action === null) ) $XML .= $this->_action->toXML();
		if ($this->_object !== null) $XML .= "<object>".recode_string("utf8..xml",$this->_object)."</object>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_actor !== null) $XML .= "<actor>".$this->_actor."</actor>\n";
		if ($this->_revoked !== null) $XML .= "<revoked>".$this->_revoked."</revoked>\n";
		$XML .= "</Privilege>\n";
		return $XML;
	}
}

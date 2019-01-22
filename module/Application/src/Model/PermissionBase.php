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
class Default_Model_PermissionBase
{
	protected $_mapper;
	protected $_id;
	protected $_actorGUID;
	protected $_actor;
	protected $_actionID;
	protected $_action;
	protected $_targetGUID;
	protected $_target;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Permission property: '$name'");
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
			throw new Exception("Invalid Permission property: '$name'");
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

	public function setActorGUID($value)
	{
		/* if ( $value === null ) {
			$this->_actorGUID = 'NULL';
		} else */ $this->_actorGUID = $value;
		return $this;
	}

	public function getActorGUID()
	{
		return $this->_actorGUID;
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


	public function setTargetGUID($value)
	{
		/* if ( $value === null ) {
			$this->_targetGUID = 'NULL';
		} else */ $this->_targetGUID = $value;
		return $this;
	}

	public function getTargetGUID()
	{
		return $this->_targetGUID;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_PermissionsMapper());
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
		$XML = "<Permission>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_actorGUID !== null) $XML .= "<actorGUID>".recode_string("utf8..xml",$this->_actorGUID)."</actorGUID>\n";
		if ( $recursive ) if ( $this->_actor === null ) $this->getActor();
		if ( ! ($this->_actor === null) ) $XML .= $this->_actor->toXML();
		if ($this->_actionID !== null) $XML .= "<actionID>".$this->_actionID."</actionID>\n";
		if ( $recursive ) if ( $this->_action === null ) $this->getAction();
		if ( ! ($this->_action === null) ) $XML .= $this->_action->toXML();
		if ($this->_targetGUID !== null) $XML .= "<targetGUID>".recode_string("utf8..xml",$this->_targetGUID)."</targetGUID>\n";
		if ( $recursive ) if ( $this->_target === null ) $this->getTarget();
		if ( ! ($this->_target === null) ) $XML .= $this->_target->toXML();
		$XML .= "</Permission>\n";
		return $XML;
	}
}

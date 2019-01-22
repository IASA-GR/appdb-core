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
class Default_Model_ActorGroupMemberBase
{
	protected $_mapper;
	protected $_id;
	protected $_groupID;
	protected $_group;
	protected $_actorGUID;
	protected $_actor;
	protected $_payload;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid ActorGroupMember property: '$name'");
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
			throw new Exception("Invalid ActorGroupMember property: '$name'");
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

	public function setGroupID($value)
	{
		/* if ( $value === null ) {
			$this->_groupID = 'NULL';
		} else */ $this->_groupID = $value;
		return $this;
	}

	public function getGroupID()
	{
		return $this->_groupID;
	}

	public function getGroup()
	{
		if ( $this->_group === null ) {
			$ActorGroups = new Default_Model_ActorGroups();
			$ActorGroups->filter->id->equals($this->getGroupID());
			if ($ActorGroups->count() > 0) $this->_group = $ActorGroups->items[0];
		}
		return $this->_group;
	}

	public function setGroup($value)
	{
		if ( $value === null ) {
			$this->setGroupID(null);
		} else {
			$this->setGroupID($value->getId());
		}
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

	public function setPayload($value)
	{
		/* if ( $value === null ) {
			$this->_payload = 'NULL';
		} else */ $this->_payload = $value;
		return $this;
	}

	public function getPayload()
	{
		return $this->_payload;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_ActorGroupMembersMapper());
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
		$XML = "<ActorGroupMember>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_groupID !== null) $XML .= "<groupID>".$this->_groupID."</groupID>\n";
		if ( $recursive ) if ( $this->_group === null ) $this->getGroup();
		if ( ! ($this->_group === null) ) $XML .= $this->_group->toXML();
		if ($this->_actorGUID !== null) $XML .= "<actorGUID>".recode_string("utf8..xml",$this->_actorGUID)."</actorGUID>\n";
		if ( $recursive ) if ( $this->_actor === null ) $this->getActor();
		if ( ! ($this->_actor === null) ) $XML .= $this->_actor->toXML();
		if ($this->_payload !== null) $XML .= "<payload>".recode_string("utf8..xml",$this->_payload)."</payload>\n";
		$XML .= "</ActorGroupMember>\n";
		return $XML;
	}
}

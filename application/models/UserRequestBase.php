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
class Default_Model_UserRequestBase
{
	protected $_mapper;
	protected $_id;
	protected $_typeID;
	protected $_requestType;
	protected $_userGUID;
	protected $_userData;
	protected $_targetGUID;
	protected $_actorGUID;
	protected $_actorData;
	protected $_stateID;
	protected $_requestState;
	protected $_created;
	protected $_lastUpdated;
	protected $_guID;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid UserRequest property: '$name'");
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
			throw new Exception("Invalid UserRequest property: '$name'");
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

	public function setTypeID($value)
	{
		/* if ( $value === null ) {
			$this->_typeID = 'NULL';
		} else */ $this->_typeID = $value;
		return $this;
	}

	public function getTypeID()
	{
		return $this->_typeID;
	}

	public function getRequestType()
	{
		if ( $this->_requestType === null ) {
			$UserRequestTypes = new Default_Model_UserRequestTypes();
			$UserRequestTypes->filter->id->equals($this->getTypeID());
			if ($UserRequestTypes->count() > 0) $this->_requestType = $UserRequestTypes->items[0];
		}
		return $this->_requestType;
	}

	public function setRequestType($value)
	{
		if ( $value === null ) {
			$this->setTypeID(null);
		} else {
			$this->setTypeID($value->getId());
		}
	}


	public function setUserGUID($value)
	{
		/* if ( $value === null ) {
			$this->_userGUID = 'NULL';
		} else */ $this->_userGUID = $value;
		return $this;
	}

	public function getUserGUID()
	{
		return $this->_userGUID;
	}

	public function setUserData($value)
	{
		/* if ( $value === null ) {
			$this->_userData = 'NULL';
		} else */ $this->_userData = $value;
		return $this;
	}

	public function getUserData()
	{
		return $this->_userData;
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

	public function setActorData($value)
	{
		/* if ( $value === null ) {
			$this->_actorData = 'NULL';
		} else */ $this->_actorData = $value;
		return $this;
	}

	public function getActorData()
	{
		return $this->_actorData;
	}

	public function setStateID($value)
	{
		/* if ( $value === null ) {
			$this->_stateID = 'NULL';
		} else */ $this->_stateID = $value;
		return $this;
	}

	public function getStateID()
	{
		return $this->_stateID;
	}

	public function getRequestState()
	{
		if ( $this->_requestState === null ) {
			$UserRequestStates = new Default_Model_UserRequestStates();
			$UserRequestStates->filter->id->equals($this->getStateID());
			if ($UserRequestStates->count() > 0) $this->_requestState = $UserRequestStates->items[0];
		}
		return $this->_requestState;
	}

	public function setRequestState($value)
	{
		if ( $value === null ) {
			$this->setStateID(null);
		} else {
			$this->setStateID($value->getId());
		}
	}


	public function setCreated($value)
	{
		/* if ( $value === null ) {
			$this->_created = 'NULL';
		} else */ $this->_created = $value;
		return $this;
	}

	public function getCreated()
	{
		return $this->_created;
	}

	public function setLastUpdated($value)
	{
		/* if ( $value === null ) {
			$this->_lastUpdated = 'NULL';
		} else */ $this->_lastUpdated = $value;
		return $this;
	}

	public function getLastUpdated()
	{
		return $this->_lastUpdated;
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

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_UserRequestsMapper());
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
		$XML = "<UserRequest>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_typeID !== null) $XML .= "<typeID>".$this->_typeID."</typeID>\n";
		if ( $recursive ) if ( $this->_requestType === null ) $this->getRequestType();
		if ( ! ($this->_requestType === null) ) $XML .= $this->_requestType->toXML();
		if ($this->_userGUID !== null) $XML .= "<userGUID>".recode_string("utf8..xml",$this->_userGUID)."</userGUID>\n";
		if ($this->_userData !== null) $XML .= "<userData>".recode_string("utf8..xml",$this->_userData)."</userData>\n";
		if ($this->_targetGUID !== null) $XML .= "<targetGUID>".$this->_targetGUID."</targetGUID>\n";
		if ($this->_actorGUID !== null) $XML .= "<actorGUID>".$this->_actorGUID."</actorGUID>\n";
		if ($this->_actorData !== null) $XML .= "<actorData>".recode_string("utf8..xml",$this->_actorData)."</actorData>\n";
		if ($this->_stateID !== null) $XML .= "<stateID>".$this->_stateID."</stateID>\n";
		if ( $recursive ) if ( $this->_requestState === null ) $this->getRequestState();
		if ( ! ($this->_requestState === null) ) $XML .= $this->_requestState->toXML();
		if ($this->_created !== null) $XML .= "<created>".recode_string("utf8..xml",$this->_created)."</created>\n";
		if ($this->_lastUpdated !== null) $XML .= "<lastUpdated>".recode_string("utf8..xml",$this->_lastUpdated)."</lastUpdated>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		$XML .= "</UserRequest>\n";
		return $XML;
	}
}

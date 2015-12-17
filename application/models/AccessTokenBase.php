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
class Default_Model_AccessTokenBase
{
	protected $_mapper;
	protected $_id;
	protected $_token;
	protected $_actorid;
	protected $_actor;
	protected $_createdOn;
	protected $_type;
	protected $_addedbyid;
	protected $_addedby;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AccessToken property: '$name'");
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
			throw new Exception("Invalid AccessToken property: '$name'");
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

	public function setToken($value)
	{
		/* if ( $value === null ) {
			$this->_token = 'NULL';
		} else */ $this->_token = $value;
		return $this;
	}

	public function getToken()
	{
		return $this->_token;
	}

	public function setActorid($value)
	{
		/* if ( $value === null ) {
			$this->_actorid = 'NULL';
		} else */ $this->_actorid = $value;
		return $this;
	}

	public function getActorid()
	{
		return $this->_actorid;
	}

	public function getActor() {
		if ($this->_actor === null) {
			$a = new Default_Model_Actors();
			$a->filter->guid->equals($this->_actorid);
			if ( count($a->items) > 0 ) {
				$this->_actor = $a->items[0];
			}
		}
		return $this->_actor;
	}

	public function setActor($value) {
		if ( $value === null ) {
			$this->_actorid = null;
		} else {
			$this->_actorid = $value->guid;
		}
		$this->_actor = $value;
	}

	public function setAddedByID($value)
	{
		/* if ( $value === null ) {
			$this->_actorid = 'NULL';
		} else */ $this->_addedbyid = $value;
		return $this;
	}

	public function getAddedByID()
	{
		return $this->_addedbyid;
	}

	public function getAddedBy() {
		if ($this->_addedby === null) {
			$a = new Default_Model_Researchers();
			$a->filter->id->equals($this->_addedbyid);
			if ( count($a->items) > 0 ) {
				$this->_addedby = $a->items[0];
			}
		}
		return $this->_addedby;
	}

	public function setAddedBy($value) {
		if ( $value === null ) {
			$this->_addedbyid = null;
		} else {
			$this->_addedbyid = $value->id;
		}
		$this->_addedby = $value;
	}


	public function setCreatedOn($value)
	{
		/* if ( $value === null ) {
			$this->_createdOn = 'NULL';
		} else */ $this->_createdOn = $value;
		return $this;
	}

	public function getCreatedOn()
	{
		return $this->_createdOn;
	}

	public function setType($value)
	{
		/* if ( $value === null ) {
			$this->_type = 'NULL';
		} else */ $this->_type = $value;
		return $this;
	}

	public function getType()
	{
		return $this->_type;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_AccessTokensMapper());
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
		$XML = "<AccessToken>\n";
		if ($this->_id !== null) $XML .= "<id>".recode_string("utf8..xml",$this->_id)."</id>\n";
		if ($this->_token !== null) $XML .= "<token>".$this->_token."</token>\n";
		if ($this->_actorid !== null) $XML .= "<actorid>".recode_string("utf8..xml",$this->_actorid)."</actorid>\n";
		if ( $recursive ) if ( $this->_actor === null ) $this->getActor();
		if ( ! ($this->_actor === null) ) $XML .= $this->_actor->toXML();
		if ($this->_createdOn !== null) $XML .= "<createdOn>".recode_string("utf8..xml",$this->_createdOn)."</createdOn>\n";
		if ($this->_type !== null) $XML .= "<type>".$this->_type."</type>\n";
		$XML .= "</AccessToken>\n";
		return $XML;
	}
}

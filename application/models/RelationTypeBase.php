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
class Default_Model_RelationTypeBase
{
	protected $_mapper;
	protected $_id;
	protected $_targetType;
	protected $_verbID;
	protected $_subjectType;
	protected $_description;
	protected $_actionID;
	protected $_action;
	protected $_guid;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid RelationType property: '$name'");
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
			throw new Exception("Invalid RelationType property: '$name'");
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

	public function setTargetType($value)
	{
		/* if ( $value === null ) {
			$this->_targetType = 'NULL';
		} else */ $this->_targetType = $value;
		return $this;
	}

	public function getTargetType()
	{
		return $this->_targetType;
	}

	public function setVerbID($value)
	{
		/* if ( $value === null ) {
			$this->_verbID = 'NULL';
		} else */ $this->_verbID = $value;
		return $this;
	}

	public function getVerbID()
	{
		return $this->_verbID;
	}

	public function setSubjectType($value)
	{
		/* if ( $value === null ) {
			$this->_subjectType = 'NULL';
		} else */ $this->_subjectType = $value;
		return $this;
	}

	public function getSubjectType()
	{
		return $this->_subjectType;
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


	public function setGuid($value)
	{
		/* if ( $value === null ) {
			$this->_guid = 'NULL';
		} else */ $this->_guid = $value;
		return $this;
	}

	public function getGuid()
	{
		return $this->_guid;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_RelationTypesMapper());
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
		$XML = "<RelationType>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_targetType !== null) $XML .= "<targetType>".recode_string("utf8..xml",$this->_targetType)."</targetType>\n";
		if ($this->_verbID !== null) $XML .= "<verbID>".$this->_verbID."</verbID>\n";
		if ($this->_subjectType !== null) $XML .= "<subjectType>".recode_string("utf8..xml",$this->_subjectType)."</subjectType>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_actionID !== null) $XML .= "<actionID>".$this->_actionID."</actionID>\n";
		if ( $recursive ) if ( $this->_action === null ) $this->getAction();
		if ( ! ($this->_action === null) ) $XML .= $this->_action->toXML();
		if ($this->_guid !== null) $XML .= "<guid>".recode_string("utf8..xml",$this->_guid)."</guid>\n";
		$XML .= "</RelationType>\n";
		return $XML;
	}
}

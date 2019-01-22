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
class Default_Model_EntityRelationBase
{
	protected $_mapper;
	protected $_id;
	protected $_relTypeID;
	protected $_reltype;
	protected $_verbID;
	protected $_verb;
	protected $_targetGUID;
	protected $_targetType;
	protected $_actionID;
	protected $_action;
	protected $_verbName;
	protected $_verbDName;
	protected $_verbRName;
	protected $_subjectGUID;
	protected $_subjectType;
	protected $_typeGUID;
	protected $_guid;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid EntityRelation property: '$name'");
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
			throw new Exception("Invalid EntityRelation property: '$name'");
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

	public function setRelTypeID($value)
	{
		/* if ( $value === null ) {
			$this->_relTypeID = 'NULL';
		} else */ $this->_relTypeID = $value;
		return $this;
	}

	public function getRelTypeID()
	{
		return $this->_relTypeID;
	}

	public function getReltype()
	{
		if ( $this->_reltype === null ) {
			$RelationTypes = new Default_Model_RelationTypes();
			$RelationTypes->filter->id->equals($this->getRelTypeID());
			if ($RelationTypes->count() > 0) $this->_reltype = $RelationTypes->items[0];
		}
		return $this->_reltype;
	}

	public function setReltype($value)
	{
		if ( $value === null ) {
			$this->setRelTypeID(null);
		} else {
			$this->setRelTypeID($value->getId());
		}
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

	public function getVerb()
	{
		if ( $this->_verb === null ) {
			$RelationVerbs = new Default_Model_RelationVerbs();
			$RelationVerbs->filter->id->equals($this->getVerbID());
			if ($RelationVerbs->count() > 0) $this->_verb = $RelationVerbs->items[0];
		}
		return $this->_verb;
	}

	public function setVerb($value)
	{
		if ( $value === null ) {
			$this->setVerbID(null);
		} else {
			$this->setVerbID($value->getId());
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


	public function setVerbName($value)
	{
		/* if ( $value === null ) {
			$this->_verbName = 'NULL';
		} else */ $this->_verbName = $value;
		return $this;
	}

	public function getVerbName()
	{
		return $this->_verbName;
	}

	public function setVerbDName($value)
	{
		/* if ( $value === null ) {
			$this->_verbDName = 'NULL';
		} else */ $this->_verbDName = $value;
		return $this;
	}

	public function getVerbDName()
	{
		return $this->_verbDName;
	}

	public function setVerbRName($value)
	{
		/* if ( $value === null ) {
			$this->_verbRName = 'NULL';
		} else */ $this->_verbRName = $value;
		return $this;
	}

	public function getVerbRName()
	{
		return $this->_verbRName;
	}

	public function setSubjectGUID($value)
	{
		/* if ( $value === null ) {
			$this->_subjectGUID = 'NULL';
		} else */ $this->_subjectGUID = $value;
		return $this;
	}

	public function getSubjectGUID()
	{
		return $this->_subjectGUID;
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

	public function setTypeGUID($value)
	{
		/* if ( $value === null ) {
			$this->_typeGUID = 'NULL';
		} else */ $this->_typeGUID = $value;
		return $this;
	}

	public function getTypeGUID()
	{
		return $this->_typeGUID;
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
			$this->setMapper(new Default_Model_EntityRelationsMapper());
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
		$XML = "<EntityRelation>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_relTypeID !== null) $XML .= "<relTypeID>".$this->_relTypeID."</relTypeID>\n";
		if ( $recursive ) if ( $this->_reltype === null ) $this->getReltype();
		if ( ! ($this->_reltype === null) ) $XML .= $this->_reltype->toXML();
		if ($this->_verbID !== null) $XML .= "<verbID>".$this->_verbID."</verbID>\n";
		if ( $recursive ) if ( $this->_verb === null ) $this->getVerb();
		if ( ! ($this->_verb === null) ) $XML .= $this->_verb->toXML();
		if ($this->_targetGUID !== null) $XML .= "<targetGUID>".recode_string("utf8..xml",$this->_targetGUID)."</targetGUID>\n";
		if ($this->_targetType !== null) $XML .= "<targetType>".$this->_targetType."</targetType>\n";
		if ($this->_actionID !== null) $XML .= "<actionID>".$this->_actionID."</actionID>\n";
		if ( $recursive ) if ( $this->_action === null ) $this->getAction();
		if ( ! ($this->_action === null) ) $XML .= $this->_action->toXML();
		if ($this->_verbName !== null) $XML .= "<verbName>".recode_string("utf8..xml",$this->_verbName)."</verbName>\n";
		if ($this->_verbDName !== null) $XML .= "<verbDName>".recode_string("utf8..xml",$this->_verbDName)."</verbDName>\n";
		if ($this->_verbRName !== null) $XML .= "<verbRName>".recode_string("utf8..xml",$this->_verbRName)."</verbRName>\n";
		if ($this->_subjectGUID !== null) $XML .= "<subjectGUID>".$this->_subjectGUID."</subjectGUID>\n";
		if ($this->_subjectType !== null) $XML .= "<subjectType>".$this->_subjectType."</subjectType>\n";
		if ($this->_typeGUID !== null) $XML .= "<typeGUID>".$this->_typeGUID."</typeGUID>\n";
		if ($this->_guid !== null) $XML .= "<guid>".$this->_guid."</guid>\n";
		$XML .= "</EntityRelation>\n";
		return $XML;
	}
}

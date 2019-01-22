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
class Default_Model_RelationBase
{
	protected $_mapper;
	protected $_id;
	protected $_relTypeID;
	protected $_relationVerb;
	protected $_targetGUID;
	protected $_subjectGUID;
	protected $_parentID;
	protected $_parent;
	protected $_addedOn;
	protected $_addedByID;
	protected $_addedBy;
	protected $_denyOn;
	protected $_denyByID;
	protected $_denyBy;
	protected $_guid;
	protected $_hiddenOn;
	protected $_hiddenByID;
	protected $_hiddenBy;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Relation property: '$name'");
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
			throw new Exception("Invalid Relation property: '$name'");
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

	public function getRelationVerb()
	{
		if ( $this->_relationVerb === null ) {
			$RelationTypes = new Default_Model_RelationTypes();
			$RelationTypes->filter->id->equals($this->getRelTypeID());
			if ($RelationTypes->count() > 0) $this->_relationVerb = $RelationTypes->items[0];
		}
		return $this->_relationVerb;
	}

	public function setRelationVerb($value)
	{
		if ( $value === null ) {
			$this->setRelTypeID(null);
		} else {
			$this->setRelTypeID($value->getId());
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

	public function setParentID($value)
	{
		/* if ( $value === null ) {
			$this->_parentID = 'NULL';
		} else */ $this->_parentID = $value;
		return $this;
	}

	public function getParentID()
	{
		return $this->_parentID;
	}

	public function getParent()
	{
		if ( $this->_parent === null ) {
			$Relations = new Default_Model_Relations();
			$Relations->filter->id->equals($this->getParentID());
			if ($Relations->count() > 0) $this->_parent = $Relations->items[0];
		}
		return $this->_parent;
	}

	public function setParent($value)
	{
		if ( $value === null ) {
			$this->setParentID(null);
		} else {
			$this->setParentID($value->getId());
		}
	}


	public function setAddedOn($value)
	{
		/* if ( $value === null ) {
			$this->_addedOn = 'NULL';
		} else */ $this->_addedOn = $value;
		return $this;
	}

	public function getAddedOn()
	{
		return $this->_addedOn;
	}

	public function setAddedByID($value)
	{
		/* if ( $value === null ) {
			$this->_addedByID = 'NULL';
		} else */ $this->_addedByID = $value;
		return $this;
	}

	public function getAddedByID()
	{
		return $this->_addedByID;
	}

	public function getAddedBy()
	{
		if ( $this->_addedBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getAddedByID());
			if ($Researchers->count() > 0) $this->_addedBy = $Researchers->items[0];
		}
		return $this->_addedBy;
	}

	public function setAddedBy($value)
	{
		if ( $value === null ) {
			$this->setAddedByID(null);
		} else {
			$this->setAddedByID($value->getId());
		}
	}


	public function setDenyOn($value)
	{
		/* if ( $value === null ) {
			$this->_denyOn = 'NULL';
		} else */ $this->_denyOn = $value;
		return $this;
	}

	public function getDenyOn()
	{
		return $this->_denyOn;
	}

	public function setDenyByID($value)
	{
		/* if ( $value === null ) {
			$this->_denyByID = 'NULL';
		} else */ $this->_denyByID = $value;
		return $this;
	}

	public function getDenyByID()
	{
		return $this->_denyByID;
	}

	public function getDenyBy()
	{
		if ( $this->_denyBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getDenyByID());
			if ($Researchers->count() > 0) $this->_denyBy = $Researchers->items[0];
		}
		return $this->_denyBy;
	}

	public function setDenyBy($value)
	{
		if ( $value === null ) {
			$this->setDenyByID(null);
		} else {
			$this->setDenyByID($value->getId());
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

	public function setHiddenOn($value)
	{
		/* if ( $value === null ) {
			$this->_hiddenOn = 'NULL';
		} else */ $this->_hiddenOn = $value;
		return $this;
	}

	public function getHiddenOn()
	{
		return $this->_hiddenOn;
	}

	public function setHiddenByID($value)
	{
		/* if ( $value === null ) {
			$this->_hiddenByID = 'NULL';
		} else */ $this->_hiddenByID = $value;
		return $this;
	}

	public function getHiddenByID()
	{
		return $this->_hiddenByID;
	}

	public function getHiddenBy()
	{
		if ( $this->_hiddenBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getHiddenByID());
			if ($Researchers->count() > 0) $this->_hiddenBy = $Researchers->items[0];
		}
		return $this->_hiddenBy;
	}

	public function setHiddenBy($value)
	{
		if ( $value === null ) {
			$this->setHiddenByID(null);
		} else {
			$this->setHiddenByID($value->getId());
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
			$this->setMapper(new Default_Model_RelationsMapper());
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
		$XML = "<Relation>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_relTypeID !== null) $XML .= "<relTypeID>".$this->_relTypeID."</relTypeID>\n";
		if ( $recursive ) if ( $this->_relationVerb === null ) $this->getRelationVerb();
		if ( ! ($this->_relationVerb === null) ) $XML .= $this->_relationVerb->toXML();
		if ($this->_targetGUID !== null) $XML .= "<targetGUID>".recode_string("utf8..xml",$this->_targetGUID)."</targetGUID>\n";
		if ($this->_subjectGUID !== null) $XML .= "<subjectGUID>".$this->_subjectGUID."</subjectGUID>\n";
		if ($this->_parentID !== null) $XML .= "<parentID>".$this->_parentID."</parentID>\n";
		if ( $recursive ) if ( $this->_parent === null ) $this->getParent();
		if ( ! ($this->_parent === null) ) $XML .= $this->_parent->toXML();
		if ($this->_addedOn !== null) $XML .= "<addedOn>".recode_string("utf8..xml",$this->_addedOn)."</addedOn>\n";
		if ($this->_addedByID !== null) $XML .= "<addedByID>".$this->_addedByID."</addedByID>\n";
		if ( $recursive ) if ( $this->_addedBy === null ) $this->getAddedBy();
		if ( ! ($this->_addedBy === null) ) $XML .= $this->_addedBy->toXML();
		if ($this->_denyOn !== null) $XML .= "<denyOn>".recode_string("utf8..xml",$this->_denyOn)."</denyOn>\n";
		if ($this->_denyByID !== null) $XML .= "<denyByID>".$this->_denyByID."</denyByID>\n";
		if ( $recursive ) if ( $this->_denyBy === null ) $this->getDenyBy();
		if ( ! ($this->_denyBy === null) ) $XML .= $this->_denyBy->toXML();
		if ($this->_guid !== null) $XML .= "<guid>".recode_string("utf8..xml",$this->_guid)."</guid>\n";
		if ($this->_hiddenOn !== null) $XML .= "<hiddenOn>".recode_string("utf8..xml",$this->_hiddenOn)."</hiddenOn>\n";
		if ($this->_hiddenByID !== null) $XML .= "<hiddenByID>".$this->_hiddenByID."</hiddenByID>\n";
		if ( $recursive ) if ( $this->_hiddenBy === null ) $this->getHiddenBy();
		if ( ! ($this->_hiddenBy === null) ) $XML .= $this->_hiddenBy->toXML();
		$XML .= "</Relation>\n";
		return $XML;
	}
}

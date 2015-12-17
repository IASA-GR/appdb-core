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
class Default_Model_DisciplineBase
{
	protected $_mapper;
	protected $_id;
	protected $_parentID;
	protected $_parent;
	protected $_name;
	protected $_ord;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Discipline property: '$name'");
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
			throw new Exception("Invalid Discipline property: '$name'");
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
			$Disciplines = new Default_Model_Disciplines();
			$Disciplines->filter->id->equals($this->getParentID());
			if ($Disciplines->count() > 0) $this->_parent = $Disciplines->items[0];
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


	public function setName($value)
	{
		/* if ( $value === null ) {
			$this->_name = 'NULL';
		} else */ $this->_name = $value;
		return $this;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function setOrd($value)
	{
		/* if ( $value === null ) {
			$this->_ord = 'NULL';
		} else */ $this->_ord = $value;
		return $this;
	}

	public function getOrd()
	{
		return $this->_ord;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_DisciplinesMapper());
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
		$XML = "<Discipline>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_parentID !== null) $XML .= "<parentID>".$this->_parentID."</parentID>\n";
		if ( $recursive ) if ( $this->_parent === null ) $this->getParent();
		if ( ! ($this->_parent === null) ) $XML .= $this->_parent->toXML();
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_ord !== null) $XML .= "<ord>".$this->_ord."</ord>\n";
		$XML .= "</Discipline>\n";
		return $XML;
	}
}

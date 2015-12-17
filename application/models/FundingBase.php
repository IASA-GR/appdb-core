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
class Default_Model_FundingBase
{
	protected $_mapper;
	protected $_id;
	protected $_identifier;
	protected $_name;
	protected $_description;
	protected $_parentID;
	protected $_parent;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Funding property: '$name'");
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
			throw new Exception("Invalid Funding property: '$name'");
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

	public function setIdentifier($value)
	{
		/* if ( $value === null ) {
			$this->_identifier = 'NULL';
		} else */ $this->_identifier = $value;
		return $this;
	}

	public function getIdentifier()
	{
		return $this->_identifier;
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
			$Fundings = new Default_Model_Fundings();
			$Fundings->filter->id->equals($this->getParentID());
			if ($Fundings->count() > 0) $this->_parent = $Fundings->items[0];
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


	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_FundingsMapper());
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
		$XML = "<Funding>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_identifier !== null) $XML .= "<identifier>".recode_string("utf8..xml",$this->_identifier)."</identifier>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_parentID !== null) $XML .= "<parentID>".$this->_parentID."</parentID>\n";
		if ( $recursive ) if ( $this->_parent === null ) $this->getParent();
		if ( ! ($this->_parent === null) ) $XML .= $this->_parent->toXML();
		$XML .= "</Funding>\n";
		return $XML;
	}
}

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
class Default_Model_DatasetBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_description;
	protected $_disciplineID;
	protected $_addedByID;
	protected $_addedBy;
	protected $_addedon;
	protected $_homepage;
	protected $_elixirURL;
	protected $_tags;
	protected $_guID;
	protected $_category;
	protected $_parentID;
	protected $_parent;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Dataset property: '$name'");
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
			throw new Exception("Invalid Dataset property: '$name'");
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

	public function setCategory($value)
	{
		/* if ( $value === null ) {
			$this->_category = 'NULL';
		} else */ $this->_category = $value;
		return $this;
	}

	public function getCategory()
	{
		return $this->_category;
	}

	public function setHomepage($value)
	{
		/* if ( $value === null ) {
			$this->_homepage = 'NULL';
		} else */ $this->_homepage = $value;
		return $this;
	}

	public function getHomepage()
	{
		return $this->_homepage;
	}

	public function setElixirURL($value)
	{
		/* if ( $value === null ) {
			$this->_elixirURL= 'NULL';
		} else */ $this->_elixirURL= $value;
		return $this;
	}

	public function getElixirURL()
	{
		return $this->_elixirURL;
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

	public function setDisciplineID($value)
	{
		/* if ( $value === null ) {
			$this->_disciplineID = 'NULL';
		} else */ $this->_disciplineID = $value;
		return $this;
	}

	public function getDisciplineID()
	{
		return $this->_disciplineID;
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
			$datasets = new Default_Model_Datasets();
			$datasets->filter->id->numequals($this->getParentID());
			if ($datasets->count() > 0) $this->_parent = $datasets->items[0];
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


	public function setAddedon($value)
	{
		/* if ( $value === null ) {
			$this->_addedon = 'NULL';
		} else */ $this->_addedon = $value;
		return $this;
	}

	public function getAddedon()
	{
		return $this->_addedon;
	}

	public function setTags($value)
	{
		/* if ( $value === null ) {
			$this->_tags = 'NULL';
		} else */ $this->_tags = $value;
		return $this;
	}

	public function getTags()
	{
		return $this->_tags;
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
			$this->setMapper(new Default_Model_DatasetsMapper());
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
		$XML = "<Dataset>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_category !== null) $XML .= "<category>".recode_string("utf8..xml",$this->_category)."</category>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_disciplineID !== null) $XML .= "<disciplineID>".$this->_disciplineID."</disciplineID>\n";
		if ($this->_addedByID !== null) $XML .= "<addedByID>".$this->_addedByID."</addedByID>\n";
		if ( $recursive ) if ( $this->_addedBy === null ) $this->getAddedBy();
		if ( ! ($this->_addedBy === null) ) $XML .= $this->_addedBy->toXML();
		if ($this->_parentID !== null) $XML .= "<parentID>".$this->_parentID."</parentID>\n";
		if ( $recursive ) if ( $this->_parent === null ) $this->getParent();
		if ( ! ($this->_parent === null) ) $XML .= $this->_parent->toXML();
		if ($this->_addedon !== null) $XML .= "<addedon>".recode_string("utf8..xml",$this->_addedon)."</addedon>\n";
		if ($this->_tags !== null) $XML .= "<tags>".recode_string("utf8..xml",$this->_tags)."</tags>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		if ($this->_homepage !== null) $XML .= "<homepage>".$this->_homepage."</homepage>\n";
		if ($this->_elixirURL !== null) $XML .= "<elixirURL>".$this->_elixirURL."</elixirURL>\n";
		$XML .= "</Dataset>\n";
		return $XML;
	}
}

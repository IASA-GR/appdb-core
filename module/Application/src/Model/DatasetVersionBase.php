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
class Default_Model_DatasetVersionBase
{
	protected $_mapper;
	protected $_id;
	protected $_datasetID;
	protected $_dataset;
	protected $_version;
	protected $_notes;
	protected $_size;
	protected $_addedByID;
	protected $_addedBy;
	protected $_addedOn;
	protected $_guID;
	protected $_parentID;
	protected $_parent;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid DatasetVersion property: '$name'");
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
			throw new Exception("Invalid DatasetVersion property: '$name'");
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

	public function setDatasetID($value)
	{
		/* if ( $value === null ) {
			$this->_datasetID = 'NULL';
		} else */ $this->_datasetID = $value;
		return $this;
	}

	public function getDatasetID()
	{
		return $this->_datasetID;
	}

	public function getDataset()
	{
		if ( $this->_dataset === null ) {
			$Datasets = new Default_Model_Datasets();
			$Datasets->filter->id->equals($this->getDatasetID());
			if ($Datasets->count() > 0) $this->_dataset = $Datasets->items[0];
		}
		return $this->_dataset;
	}

	public function setDataset($value)
	{
		if ( $value === null ) {
			$this->setDatasetID(null);
		} else {
			$this->setDatasetID($value->getId());
		}
	}


	public function setVersion($value)
	{
		/* if ( $value === null ) {
			$this->_version = 'NULL';
		} else */ $this->_version = $value;
		return $this;
	}

	public function getVersion()
	{
		return $this->_version;
	}

	public function setNotes($value)
	{
		/* if ( $value === null ) {
			$this->_notes = 'NULL';
		} else */ $this->_notes = $value;
		return $this;
	}

	public function getNotes()
	{
		return $this->_notes;
	}

	public function setSize($value)
	{
		/* if ( $value === null ) {
			$this->_size = 'NULL';
		} else */ $this->_size = $value;
		return $this;
	}

	public function getSize()
	{
		return $this->_size;
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
			$ds = new Default_Model_DatasetVersions();
			$ds->filter->id->equals($this->getParentID());
			if ($ds->count() > 0) $this->_parent = $ds->items[0];
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
			$this->setMapper(new Default_Model_DatasetVersionsMapper());
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
		$XML = "<DatasetVersion>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_datasetID !== null) $XML .= "<datasetID>".$this->_datasetID."</datasetID>\n";
		if ( $recursive ) if ( $this->_dataset === null ) $this->getDataset();
		if ( ! ($this->_dataset === null) ) $XML .= $this->_dataset->toXML();
		if ($this->_version !== null) $XML .= "<version>".recode_string("utf8..xml",$this->_version)."</version>\n";
		if ($this->_notes !== null) $XML .= "<notes>".recode_string("utf8..xml",$this->_notes)."</notes>\n";
		if ($this->_size !== null) $XML .= "<size>".$this->_size."</size>\n";
		if ($this->_addedByID !== null) $XML .= "<addedByID>".$this->_addedByID."</addedByID>\n";
		if ( $recursive ) if ( $this->_addedByID === null ) $this->getAddedByID();
		if ( ! ($this->_addedByID === null) ) $XML .= $this->_addedByID->toXML();
		if ($this->_parentID !== null) $XML .= "<parentID>".$this->_parentID."</parentID>\n";
		if ( $recursive ) if ( $this->_parentID === null ) $this->getParentID();
		if ( ! ($this->_parentID === null) ) $XML .= $this->_parentID->toXML();
		if ($this->_addedOn !== null) $XML .= "<addedOn>".recode_string("utf8..xml",$this->_addedOn)."</addedOn>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		$XML .= "</DatasetVersion>\n";
		return $XML;
	}
}

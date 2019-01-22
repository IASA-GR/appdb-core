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
class Default_Model_CategoryInfoEntryBase
{
	protected $_mapper;
	protected $_id;
	protected $_categoryID;
	protected $_category;
	protected $_type;
	protected $_data;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid CategoryInfoEntry property: '$name'");
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
			throw new Exception("Invalid CategoryInfoEntry property: '$name'");
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

	public function setCategoryID($value)
	{
		/* if ( $value === null ) {
			$this->_categoryID = 'NULL';
		} else */ $this->_categoryID = $value;
		return $this;
	}

	public function getCategoryID()
	{
		return $this->_categoryID;
	}

	public function getCategory()
	{
		if ( $this->_category === null ) {
			$Categories = new Default_Model_Categories();
			$Categories->filter->id->equals($this->getCategoryID());
			if ($Categories->count() > 0) $this->_category = $Categories->items[0];
		}
		return $this->_category;
	}

	public function setCategory($value)
	{
		if ( $value === null ) {
			$this->setCategoryID(null);
		} else {
			$this->setCategoryID($value->getId());
		}
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

	public function setData($value)
	{
		/* if ( $value === null ) {
			$this->_data = 'NULL';
		} else */ $this->_data = $value;
		return $this;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_CategoryInfoMapper());
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
		$XML = "<CategoryInfoEntry>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_categoryID !== null) $XML .= "<categoryID>".$this->_categoryID."</categoryID>\n";
		if ( $recursive ) if ( $this->_category === null ) $this->getCategory();
		if ( ! ($this->_category === null) ) $XML .= $this->_category->toXML();
		if ($this->_type !== null) $XML .= "<type>".$this->_type."</type>\n";
		if ($this->_data !== null) $XML .= "<data>".recode_string("utf8..xml",$this->_data)."</data>\n";
		$XML .= "</CategoryInfoEntry>\n";
		return $XML;
	}
}

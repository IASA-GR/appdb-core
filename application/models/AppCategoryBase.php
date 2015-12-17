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
class Default_Model_AppCategoryBase
{
	protected $_mapper;
	protected $_id;
	protected $_appID;
	protected $_application;
	protected $_categoryID;
	protected $_category;
	protected $_isPrimary;
	protected $_inherited;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AppCategory property: '$name'");
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
			throw new Exception("Invalid AppCategory property: '$name'");
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

	public function setAppID($value)
	{
		/* if ( $value === null ) {
			$this->_appID = 'NULL';
		} else */ $this->_appID = $value;
		return $this;
	}

	public function getAppID()
	{
		return $this->_appID;
	}

	public function getApplication()
	{
		if ( $this->_application === null ) {
			$Applications = new Default_Model_Applications();
			$Applications->filter->id->equals($this->getAppID());
			if ($Applications->count() > 0) $this->_application = $Applications->items[0];
		}
		return $this->_application;
	}

	public function setApplication($value)
	{
		if ( $value === null ) {
			$this->setAppID(null);
		} else {
			$this->setAppID($value->getId());
		}
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


	public function setIsPrimary($value)
	{
		/* if ( $value === null ) {
			$this->_isPrimary = 'NULL';
		} else */ $this->_isPrimary = $value;
		return $this;
	}

	public function getIsPrimary()
	{
		$v = $this->_isPrimary;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
		}
	}

	public function setInherited($value)
	{
		/* if ( $value === null ) {
			$this->_inherited = 'NULL';
		} else */ $this->_inherited = $value;
		return $this;
	}

	public function getInherited()
	{
		$v = $this->_inherited;
		if ( ($v === 1 ) || ($v === '1') || ($v === 't') || ($v === 'T') || ($v === 'true') || ($v === 'TRUE') || ($v === true) ) {
			return true;
		} elseif (isnull($v)) {
		    return null;
		} else {
			return false;
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
			$this->setMapper(new Default_Model_AppCategoriesMapper());
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
		$XML = "<AppCategory>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ( $recursive ) if ( $this->_application === null ) $this->getApplication();
		if ( ! ($this->_application === null) ) $XML .= $this->_application->toXML();
		if ($this->_categoryID !== null) $XML .= "<categoryID>".$this->_categoryID."</categoryID>\n";
		if ( $recursive ) if ( $this->_category === null ) $this->getCategory();
		if ( ! ($this->_category === null) ) $XML .= $this->_category->toXML();
		if ($this->_isPrimary !== null) $XML .= "<isPrimary>".$this->_isPrimary."</isPrimary>\n";
		if ($this->_inherited !== null) $XML .= "<inherited>".$this->_inherited."</inherited>\n";
		$XML .= "</AppCategory>\n";
		return $XML;
	}
}

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
class Default_Model_HarvestProjectContactPersonBase
{
	protected $_mapper;
	protected $_projectID;
	protected $_project;
	protected $_contactPersonID;
	protected $_contactPerson;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid HarvestProjectContactPerson property: '$name'");
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
			throw new Exception("Invalid HarvestProjectContactPerson property: '$name'");
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

	public function setProjectID($value)
	{
		/* if ( $value === null ) {
			$this->_projectID = 'NULL';
		} else */ $this->_projectID = $value;
		return $this;
	}

	public function getProjectID()
	{
		return $this->_projectID;
	}

	public function getProject()
	{
		if ( $this->_project === null ) {
			$Projects = new Default_Model_Projects();
			$Projects->filter->id->equals($this->getProjectID());
			if ($Projects->count() > 0) $this->_project = $Projects->items[0];
		}
		return $this->_project;
	}

	public function setProject($value)
	{
		if ( $value === null ) {
			$this->setProjectID(null);
		} else {
			$this->setProjectID($value->getId());
		}
	}


	public function setContactPersonID($value)
	{
		/* if ( $value === null ) {
			$this->_contactPersonID = 'NULL';
		} else */ $this->_contactPersonID = $value;
		return $this;
	}

	public function getContactPersonID()
	{
		return $this->_contactPersonID;
	}

	public function setContactPerson($value)
	{
		/* if ( $value === null ) {
			$this->_contactPersonID = 'NULL';
		} else */ $this->_contactPerson = $value;
		return $this;
	}

	public function getContactPerson()
	{
		return $this->_contactPerson;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_HarvestProjectContactPersonsMapper());
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
		$XML = "<HarvestProjectContactPerson>\n";
		if ($this->_projectID !== null) $XML .= "<projectID>".$this->_projectID."</projectID>\n";
		if ( $recursive ) if ( $this->_project === null ) $this->getProject();
		if ( ! ($this->_project === null) ) $XML .= $this->_project->toXML();
		if ($this->_contactPersonID !== null) $XML .= "<contactPersonID>".$this->_contactPersonID."</contactPersonID>\n";
		$XML .= "</HarvestProjectContactPerson>\n";
		return $XML;
	}
}

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
class Default_Model_NewBase
{
	protected $_mapper;
	protected $_id;
	protected $_timestamp;
	protected $_action;
	protected $_subjectguid;
	protected $_fields;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid New property: '$name'");
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
			throw new Exception("Invalid New property: '$name'");
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

	public function setTimestamp($value)
	{
		/* if ( $value === null ) {
			$this->_timestamp = 'NULL';
		} else */ $this->_timestamp = $value;
		return $this;
	}

	public function getTimestamp()
	{
		return $this->_timestamp;
	}

	public function setAction($value)
	{
		/* if ( $value === null ) {
			$this->_action = 'NULL';
		} else */ $this->_action = $value;
		return $this;
	}

	public function getAction()
	{
		return $this->_action;
	}

	public function setSubjectguid($value)
	{
		/* if ( $value === null ) {
			$this->_subjectguid = 'NULL';
		} else */ $this->_subjectguid = $value;
		return $this;
	}

	public function getSubjectguid()
	{
		return $this->_subjectguid;
	}

	public function setFields($value)
	{
		/* if ( $value === null ) {
			$this->_fields = 'NULL';
		} else */ $this->_fields = $value;
		return $this;
	}

	public function getFields()
	{
		return $this->_fields;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_NewsMapper());
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
		$XML = "<New>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_timestamp !== null) $XML .= "<timestamp>".recode_string("utf8..xml",$this->_timestamp)."</timestamp>\n";
		if ($this->_action !== null) $XML .= "<action>".recode_string("utf8..xml",$this->_action)."</action>\n";
		if ($this->_subjectguid !== null) $XML .= "<subjectguid>".recode_string("utf8..xml",$this->_subjectguid)."</subjectguid>\n";
		if ($this->_fields !== null) $XML .= "<fields>".recode_string("utf8..xml",$this->_fields)."</fields>\n";
		$XML .= "</New>\n";
		return $XML;
	}
}

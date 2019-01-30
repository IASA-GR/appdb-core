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
namespace Application\Model;

class AggregateNewsEntryBase
{
	protected $_mapper;
	protected $_id;
	protected $_timestamp;
	protected $_action;
	protected $_subjectGUID;
	protected $_subjectID;
	protected $_subjectName;
	protected $_subjectType;
	protected $_fields;
	protected $_subjectData;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AggregateNewsEntry property: '$name'");
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
			throw new Exception("Invalid AggregateNewsEntry property: '$name'");
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

	public function setSubjectID($value)
	{
		/* if ( $value === null ) {
			$this->_subjectID = 'NULL';
		} else */ $this->_subjectID = $value;
		return $this;
	}

	public function getSubjectID()
	{
		return $this->_subjectID;
	}

	public function setSubjectName($value)
	{
		/* if ( $value === null ) {
			$this->_subjectName = 'NULL';
		} else */ $this->_subjectName = $value;
		return $this;
	}

	public function getSubjectName()
	{
		return $this->_subjectName;
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

	public function setSubjectData($value)
	{
		/* if ( $value === null ) {
			$this->_subjectData = 'NULL';
		} else */ $this->_subjectData = $value;
		return $this;
	}

	public function getSubjectData()
	{
		return $this->_subjectData;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new AggregateNewsMapper());
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
		$XML = "<AggregateNewsEntry>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_timestamp !== null) $XML .= "<timestamp>".recode_string("utf8..xml",$this->_timestamp)."</timestamp>\n";
		if ($this->_action !== null) $XML .= "<action>".recode_string("utf8..xml",$this->_action)."</action>\n";
		if ($this->_subjectGUID !== null) $XML .= "<subjectGUID>".recode_string("utf8..xml",$this->_subjectGUID)."</subjectGUID>\n";
		if ($this->_subjectID !== null) $XML .= "<subjectID>".$this->_subjectID."</subjectID>\n";
		if ($this->_subjectName !== null) $XML .= "<subjectName>".recode_string("utf8..xml",$this->_subjectName)."</subjectName>\n";
		if ($this->_subjectType !== null) $XML .= "<subjectType>".recode_string("utf8..xml",$this->_subjectType)."</subjectType>\n";
		if ($this->_fields !== null) $XML .= "<fields>".recode_string("utf8..xml",$this->_fields)."</fields>\n";
		if ($this->_subjectData !== null) $XML .= "<subjectData>".recode_string("utf8..xml",$this->_subjectData)."</subjectData>\n";
		$XML .= "</AggregateNewsEntry>\n";
		return $XML;
	}
}

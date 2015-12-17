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
class Default_Model_VMIBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_description;
	protected $_guID;
	protected $_vappID;
	protected $_va;
	protected $_notes;
	protected $_groupname;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VMI property: '$name'");
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
			throw new Exception("Invalid VMI property: '$name'");
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

	public function setVappID($value)
	{
		/* if ( $value === null ) {
			$this->_vappID = 'NULL';
		} else */ $this->_vappID = $value;
		return $this;
	}

	public function getVappID()
	{
		return $this->_vappID;
	}

	public function getVa()
	{
		if ( $this->_va === null ) {
			$VAs = new Default_Model_VAs();
			$VAs->filter->id->equals($this->getVappID());
			if ($VAs->count() > 0) $this->_va = $VAs->items[0];
		}
		return $this->_va;
	}

	public function setVa($value)
	{
		if ( $value === null ) {
			$this->setVappID(null);
		} else {
			$this->setVappID($value->getId());
		}
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

	public function setGroupname($value)
	{
		/* if ( $value === null ) {
			$this->_groupname = 'NULL';
		} else */ $this->_groupname = $value;
		return $this;
	}

	public function getGroupname()
	{
		return $this->_groupname;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_VMIsMapper());
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
		$XML = "<VMI>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		if ($this->_vappID !== null) $XML .= "<vappID>".$this->_vappID."</vappID>\n";
		if ( $recursive ) if ( $this->_va === null ) $this->getVa();
		if ( ! ($this->_va === null) ) $XML .= $this->_va->toXML();
		if ($this->_notes !== null) $XML .= "<notes>".recode_string("utf8..xml",$this->_notes)."</notes>\n";
		if ($this->_groupname !== null) $XML .= "<groupname>".recode_string("utf8..xml",$this->_groupname)."</groupname>\n";
		$XML .= "</VMI>\n";
		return $XML;
	}
}

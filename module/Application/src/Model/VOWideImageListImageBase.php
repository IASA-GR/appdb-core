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
class Default_Model_VOWideImageListImageBase
{
	protected $_mapper;
	protected $_id;
	protected $_vowideImageListID;
	protected $_vowideImageList;
	protected $_vappListID;
	protected $_vappList;
	protected $_guid;
	protected $_state;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VOWideImageListImage property: '$name'");
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
			throw new Exception("Invalid VOWideImageListImage property: '$name'");
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

	public function setVowideImageListID($value)
	{
		/* if ( $value === null ) {
			$this->_vowideImageListID = 'NULL';
		} else */ $this->_vowideImageListID = $value;
		return $this;
	}

	public function getVowideImageListID()
	{
		return $this->_vowideImageListID;
	}

	public function getVowideImageList()
	{
		if ( $this->_vowideImageList === null ) {
			$VOWideImageLists = new Default_Model_VOWideImageLists();
			$VOWideImageLists->filter->id->equals($this->getVowideImageListID());
			if ($VOWideImageLists->count() > 0) $this->_vowideImageList = $VOWideImageLists->items[0];
		}
		return $this->_vowideImageList;
	}

	public function setVowideImageList($value)
	{
		if ( $value === null ) {
			$this->setVowideImageListID(null);
		} else {
			$this->setVowideImageListID($value->getId());
		}
	}


	public function setVappListID($value)
	{
		/* if ( $value === null ) {
			$this->_vappListID = 'NULL';
		} else */ $this->_vappListID = $value;
		return $this;
	}

	public function getVappListID()
	{
		return $this->_vappListID;
	}

	public function getVappList()
	{
		if ( $this->_vappList === null ) {
			$VALists = new Default_Model_VALists();
			$VALists->filter->id->equals($this->getVappListID());
			if ($VALists->count() > 0) $this->_vappList = $VALists->items[0];
		}
		return $this->_vappList;
	}

	public function setVappList($value)
	{
		if ( $value === null ) {
			$this->setVappListID(null);
		} else {
			$this->setVappListID($value->getId());
		}
	}


	public function setGuid($value)
	{
		/* if ( $value === null ) {
			$this->_guid = 'NULL';
		} else */ $this->_guid = $value;
		return $this;
	}

	public function getGuid()
	{
		return $this->_guid;
	}

	public function setState($value)
	{
		/* if ( $value === null ) {
			$this->_state = 'NULL';
		} else */ $this->_state = $value;
		return $this;
	}

	public function getState()
	{
		return $this->_state;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_VOWideImageListImagesMapper());
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
		$XML = "<VOWideImageListImage>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_vowideImageListID !== null) $XML .= "<vowideImageListID>".$this->_vowideImageListID."</vowideImageListID>\n";
		if ( $recursive ) if ( $this->_vowideImageList === null ) $this->getVowideImageList();
		if ( ! ($this->_vowideImageList === null) ) $XML .= $this->_vowideImageList->toXML();
		if ($this->_vappListID !== null) $XML .= "<vappListID>".$this->_vappListID."</vappListID>\n";
		if ( $recursive ) if ( $this->_vappList === null ) $this->getVappList();
		if ( ! ($this->_vappList === null) ) $XML .= $this->_vappList->toXML();
		if ($this->_guid !== null) $XML .= "<guid>".recode_string("utf8..xml",$this->_guid)."</guid>\n";
		if ( ! ($this->_state === null) ) $XML .= $this->_state->toXML();
		$XML .= "</VOWideImageListImage>\n";
		return $XML;
	}
}

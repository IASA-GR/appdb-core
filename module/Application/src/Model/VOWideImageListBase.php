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
class Default_Model_VOWideImageListBase
{
	protected $_mapper;
	protected $_id;
	protected $_voID;
	protected $_vo;
	protected $_guid;
	protected $_state;
	protected $_expiresOn;
	protected $_publishedOn;
	protected $_notes;
	protected $_title;
	protected $_alteredByID;
	protected $_alteredBy;
	protected $_lastModified;
	protected $_publishedByID;
	protected $_publishedBy;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VOWideImageList property: '$name'");
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
			throw new Exception("Invalid VOWideImageList property: '$name'");
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

	public function setVoID($value)
	{
		/* if ( $value === null ) {
			$this->_voID = 'NULL';
		} else */ $this->_voID = $value;
		return $this;
	}

	public function getVoID()
	{
		return $this->_voID;
	}

	public function getVo()
	{
		if ( $this->_vo === null ) {
			$VOs = new Default_Model_VOs();
			$VOs->filter->id->equals($this->getVoID());
			if ($VOs->count() > 0) $this->_vo = $VOs->items[0];
		}
		return $this->_vo;
	}

	public function setVo($value)
	{
		if ( $value === null ) {
			$this->setVoID(null);
		} else {
			$this->setVoID($value->getId());
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

	public function setExpiresOn($value)
	{
		/* if ( $value === null ) {
			$this->_expiresOn = 'NULL';
		} else */ $this->_expiresOn = $value;
		return $this;
	}

	public function getExpiresOn()
	{
		return $this->_expiresOn;
	}

	public function setPublishedOn($value)
	{
		/* if ( $value === null ) {
			$this->_publishedOn = 'NULL';
		} else */ $this->_publishedOn = $value;
		return $this;
	}

	public function getPublishedOn()
	{
		return $this->_publishedOn;
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

	public function setTitle($value)
	{
		/* if ( $value === null ) {
			$this->_title = 'NULL';
		} else */ $this->_title = $value;
		return $this;
	}

	public function getTitle()
	{
		return $this->_title;
	}

	public function setAlteredByID($value)
	{
		/* if ( $value === null ) {
			$this->_alteredByID = 'NULL';
		} else */ $this->_alteredByID = $value;
		return $this;
	}

	public function getAlteredByID()
	{
		return $this->_alteredByID;
	}

	public function getAlteredBy()
	{
		if ( $this->_alteredBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getAlteredByID());
			if ($Researchers->count() > 0) $this->_alteredBy = $Researchers->items[0];
		}
		return $this->_alteredBy;
	}

	public function setAlteredBy($value)
	{
		if ( $value === null ) {
			$this->setAlteredByID(null);
		} else {
			$this->setAlteredByID($value->getId());
		}
	}


	public function setLastModified($value)
	{
		/* if ( $value === null ) {
			$this->_lastModified = 'NULL';
		} else */ $this->_lastModified = $value;
		return $this;
	}

	public function getLastModified()
	{
		return $this->_lastModified;
	}

	public function setPublishedByID($value)
	{
		/* if ( $value === null ) {
			$this->_publishedByID = 'NULL';
		} else */ $this->_publishedByID = $value;
		return $this;
	}

	public function getPublishedByID()
	{
		return $this->_publishedByID;
	}

	public function getPublishedBy()
	{
		if ( $this->_publishedBy === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getPublishedByID());
			if ($Researchers->count() > 0) $this->_publishedBy = $Researchers->items[0];
		}
		return $this->_publishedBy;
	}

	public function setPublishedBy($value)
	{
		if ( $value === null ) {
			$this->setPublishedByID(null);
		} else {
			$this->setPublishedByID($value->getId());
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
			$this->setMapper(new Default_Model_VOWideImageListsMapper());
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
		$XML = "<VOWideImageList>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_voID !== null) $XML .= "<voID>".$this->_voID."</voID>\n";
		if ( $recursive ) if ( $this->_vo === null ) $this->getVo();
		if ( ! ($this->_vo === null) ) $XML .= $this->_vo->toXML();
		if ($this->_guid !== null) $XML .= "<guid>".recode_string("utf8..xml",$this->_guid)."</guid>\n";
		if ($this->_state !== null) $XML .= "<state>".$this->_state."</state>\n";
		if ($this->_expiresOn !== null) $XML .= "<expiresOn>".recode_string("utf8..xml",$this->_expiresOn)."</expiresOn>\n";
		if ($this->_publishedOn !== null) $XML .= "<publishedOn>".recode_string("utf8..xml",$this->_publishedOn)."</publishedOn>\n";
		if ($this->_notes !== null) $XML .= "<notes>".recode_string("utf8..xml",$this->_notes)."</notes>\n";
		if ($this->_title !== null) $XML .= "<title>".recode_string("utf8..xml",$this->_title)."</title>\n";
		if ($this->_alteredByID !== null) $XML .= "<alteredByID>".$this->_alteredByID."</alteredByID>\n";
		if ( $recursive ) if ( $this->_alteredBy === null ) $this->getAlteredBy();
		if ( ! ($this->_alteredBy === null) ) $XML .= $this->_alteredBy->toXML();
		if ($this->_lastModified !== null) $XML .= "<lastModified>".recode_string("utf8..xml",$this->_lastModified)."</lastModified>\n";
		if ($this->_publishedByID !== null) $XML .= "<publishedByID>".$this->_publishedByID."</publishedByID>\n";
		if ( $recursive ) if ( $this->_publishedBy === null ) $this->getPublishedBy();
		if ( ! ($this->_publishedBy === null) ) $XML .= $this->_publishedBy->toXML();
		$XML .= "</VOWideImageList>\n";
		return $XML;
	}
}

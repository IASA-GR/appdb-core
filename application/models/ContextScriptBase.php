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
class Default_Model_ContextScriptBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_title;
	protected $_description;
	protected $_url;
	protected $_formatID;
	protected $_contextFormat;
	protected $_checksum;
	protected $_checksumfunc;
	protected $_size;
	protected $_guID;
	protected $_addedByID;
	protected $_addedby;
	protected $_addedOn;
	protected $_lastupdatedByID;
	protected $_lastupdatedOn;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid ContextScript property: '$name'");
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
			throw new Exception("Invalid ContextScript property: '$name'");
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

	public function setUrl($value)
	{
		/* if ( $value === null ) {
			$this->_url = 'NULL';
		} else */ $this->_url = $value;
		return $this;
	}

	public function getUrl()
	{
		return $this->_url;
	}

	public function setFormatID($value)
	{
		/* if ( $value === null ) {
			$this->_formatID = 'NULL';
		} else */ $this->_formatID = $value;
		return $this;
	}

	public function getFormatID()
	{
		return $this->_formatID;
	}

	public function getContextFormat()
	{
		if ( $this->_contextFormat === null ) {
			$ContextFormats = new Default_Model_ContextFormats();
			$ContextFormats->filter->id->equals($this->getFormatID());
			if ($ContextFormats->count() > 0) $this->_contextFormat = $ContextFormats->items[0];
		}
		return $this->_contextFormat;
	}

	public function setContextFormat($value)
	{
		if ( $value === null ) {
			$this->setFormatID(null);
		} else {
			$this->setFormatID($value->getId());
		}
	}


	public function setChecksum($value)
	{
		/* if ( $value === null ) {
			$this->_checksum = 'NULL';
		} else */ $this->_checksum = $value;
		return $this;
	}

	public function getChecksum()
	{
		return $this->_checksum;
	}

	public function setChecksumfunc($value)
	{
		/* if ( $value === null ) {
			$this->_checksumfunc = 'NULL';
		} else */ $this->_checksumfunc = $value;
		return $this;
	}

	public function getChecksumfunc()
	{
		return $this->_checksumfunc;
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

	public function getAddedby()
	{
		if ( $this->_addedby === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getAddedByID());
			if ($Researchers->count() > 0) $this->_addedby = $Researchers->items[0];
		}
		return $this->_addedby;
	}

	public function setAddedby($value)
	{
		if ( $value === null ) {
			$this->setAddedByID(null);
		} else {
			$this->setAddedByID($value->getId());
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

	public function setLastupdatedByID($value)
	{
		/* if ( $value === null ) {
			$this->_lastupdatedByID = 'NULL';
		} else */ $this->_lastupdatedByID = $value;
		return $this;
	}

	public function getLastupdatedByID()
	{
		return $this->_lastupdatedByID;
	}

	public function getLastupdatedBy()
	{
		if ( $this->_lastupdatedByID === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getLastupdatedByID());
			if ($Researchers->count() > 0) $this->_lastupdatedByID = $Researchers->items[0];
		}
		return $this->_lastupdatedByID;
	}

	public function setLastupdatedBy($value)
	{
		if ( $value === null ) {
			$this->setLastupdatedByID(null);
		} else {
			$this->setLastupdatedByID($value->getId());
		}
	}


	public function setLastupdatedOn($value)
	{
		/* if ( $value === null ) {
			$this->_lastupdatedOn = 'NULL';
		} else */ $this->_lastupdatedOn = $value;
		return $this;
	}

	public function getLastupdatedOn()
	{
		return $this->_lastupdatedOn;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_ContextScriptsMapper());
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
		$XML = "<ContextScript>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_title !== null) $XML .= "<title>".recode_string("utf8..xml",$this->_title)."</title>\n";
		if ($this->_description !== null) $XML .= "<description>".recode_string("utf8..xml",$this->_description)."</description>\n";
		if ($this->_url !== null) $XML .= "<url>".recode_string("utf8..xml",$this->_url)."</url>\n";
		if ($this->_formatID !== null) $XML .= "<formatID>".$this->_formatID."</formatID>\n";
		if ( $recursive ) if ( $this->_contextFormat === null ) $this->getContextFormat();
		if ( ! ($this->_contextFormat === null) ) $XML .= $this->_contextFormat->toXML();
		if ($this->_checksum !== null) $XML .= "<checksum>".recode_string("utf8..xml",$this->_checksum)."</checksum>\n";
		if ($this->_checksumfunc !== null) $XML .= "<checksumfunc>".$this->_checksumfunc."</checksumfunc>\n";
		if ($this->_size !== null) $XML .= "<size>".$this->_size."</size>\n";
		if ($this->_guID !== null) $XML .= "<guID>".$this->_guID."</guID>\n";
		if ($this->_addedByID !== null) $XML .= "<addedByID>".$this->_addedByID."</addedByID>\n";
		if ( $recursive ) if ( $this->_addedby === null ) $this->getAddedby();
		if ( ! ($this->_addedby === null) ) $XML .= $this->_addedby->toXML();
		if ($this->_addedOn !== null) $XML .= "<addedOn>".recode_string("utf8..xml",$this->_addedOn)."</addedOn>\n";
		if ($this->_lastupdatedByID !== null) $XML .= "<lastupdatedByID>".$this->_lastupdatedByID."</lastupdatedByID>\n";
		if ( $recursive ) if ( $this->_lastupdatedByID === null ) $this->getLastupdatedByID();
		if ( ! ($this->_lastupdatedByID === null) ) $XML .= $this->_lastupdatedByID->toXML();
		if ($this->_lastupdatedOn !== null) $XML .= "<lastupdatedOn>".recode_string("utf8..xml",$this->_lastupdatedOn)."</lastupdatedOn>\n";
		$XML .= "</ContextScript>\n";
		return $XML;
	}
}

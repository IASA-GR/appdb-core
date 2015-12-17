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
class Default_Model_DisseminationEntryBase
{
	protected $_mapper;
	protected $_id;
	protected $_composerID;
	protected $_composer;
	protected $_recipients;
	protected $_filter;
	protected $_subject;
	protected $_message;
	protected $_sentOn;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid DisseminationEntry property: '$name'");
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
			throw new Exception("Invalid DisseminationEntry property: '$name'");
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

	public function setComposerID($value)
	{
		/* if ( $value === null ) {
			$this->_composerID = 'NULL';
		} else */ $this->_composerID = $value;
		return $this;
	}

	public function getComposerID()
	{
		return $this->_composerID;
	}

	public function getComposer()
	{
		if ( $this->_composer === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getComposerID());
			if ($Researchers->count() > 0) $this->_composer = $Researchers->items[0];
		}
		return $this->_composer;
	}

	public function setComposer($value)
	{
		if ( $value === null ) {
			$this->setComposerID(null);
		} else {
			$this->setComposerID($value->getId());
		}
	}


	public function setRecipients($value)
	{
		/* if ( $value === null ) {
			$this->_recipients = 'NULL';
		} else */ $this->_recipients = $value;
		return $this;
	}

	public function getRecipients()
	{
		return $this->_recipients;
	}

	public function setFilter($value)
	{
		/* if ( $value === null ) {
			$this->_filter = 'NULL';
		} else */ $this->_filter = $value;
		return $this;
	}

	public function getFilter()
	{
		return $this->_filter;
	}

	public function setSubject($value)
	{
		/* if ( $value === null ) {
			$this->_subject = 'NULL';
		} else */ $this->_subject = $value;
		return $this;
	}

	public function getSubject()
	{
		return $this->_subject;
	}

	public function setMessage($value)
	{
		/* if ( $value === null ) {
			$this->_message = 'NULL';
		} else */ $this->_message = $value;
		return $this;
	}

	public function getMessage()
	{
		return $this->_message;
	}

	public function setSentOn($value)
	{
		/* if ( $value === null ) {
			$this->_sentOn = 'NULL';
		} else */ $this->_sentOn = $value;
		return $this;
	}

	public function getSentOn()
	{
		return $this->_sentOn;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_DisseminationMapper());
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
		$XML = "<DisseminationEntry>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_composerID !== null) $XML .= "<composerID>".$this->_composerID."</composerID>\n";
		if ( $recursive ) if ( $this->_composer === null ) $this->getComposer();
		if ( ! ($this->_composer === null) ) $XML .= $this->_composer->toXML();
		if ($this->_recipients !== null) $XML .= "<recipients>".$this->_recipients."</recipients>\n";
		if ($this->_filter !== null) $XML .= "<filter>".recode_string("utf8..xml",$this->_filter)."</filter>\n";
		if ($this->_subject !== null) $XML .= "<subject>".recode_string("utf8..xml",$this->_subject)."</subject>\n";
		if ($this->_message !== null) $XML .= "<message>".recode_string("utf8..xml",$this->_message)."</message>\n";
		if ($this->_sentOn !== null) $XML .= "<sentOn>".recode_string("utf8..xml",$this->_sentOn)."</sentOn>\n";
		$XML .= "</DisseminationEntry>\n";
		return $XML;
	}
}

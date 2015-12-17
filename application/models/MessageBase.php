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
class Default_Model_MessageBase
{
	protected $_mapper;
	protected $_id;
	protected $_receiverID;
	protected $_receiver;
	protected $_senderID;
	protected $_sender;
	protected $_msg;
	protected $_sentOn;
	protected $_isRead;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid Message property: '$name'");
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
			throw new Exception("Invalid Message property: '$name'");
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

	public function setReceiverID($value)
	{
		/* if ( $value === null ) {
			$this->_receiverID = 'NULL';
		} else */ $this->_receiverID = $value;
		return $this;
	}

	public function getReceiverID()
	{
		return $this->_receiverID;
	}

	public function getReceiver()
	{
		if ( $this->_receiver === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getReceiverID());
			if ($Researchers->count() > 0) $this->_receiver = $Researchers->items[0];
		}
		return $this->_receiver;
	}

	public function setReceiver($value)
	{
		if ( $value === null ) {
			$this->setReceiverID(null);
		} else {
			$this->setReceiverID($value->getId());
		}
	}


	public function setSenderID($value)
	{
		/* if ( $value === null ) {
			$this->_senderID = 'NULL';
		} else */ $this->_senderID = $value;
		return $this;
	}

	public function getSenderID()
	{
		return $this->_senderID;
	}

	public function getSender()
	{
		if ( $this->_sender === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getSenderID());
			if ($Researchers->count() > 0) $this->_sender = $Researchers->items[0];
		}
		return $this->_sender;
	}

	public function setSender($value)
	{
		if ( $value === null ) {
			$this->setSenderID(null);
		} else {
			$this->setSenderID($value->getId());
		}
	}


	public function setMsg($value)
	{
		/* if ( $value === null ) {
			$this->_msg = 'NULL';
		} else */ $this->_msg = $value;
		return $this;
	}

	public function getMsg()
	{
		return $this->_msg;
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

	public function setIsRead($value)
	{
		/* if ( $value === null ) {
			$this->_isRead = 'NULL';
		} else */ $this->_isRead = $value;
		return $this;
	}

	public function getIsRead()
	{
		$v = $this->_isRead;
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
			$this->setMapper(new Default_Model_MessagesMapper());
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
		$XML = "<Message>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_receiverID !== null) $XML .= "<receiverID>".$this->_receiverID."</receiverID>\n";
		if ( $recursive ) if ( $this->_receiver === null ) $this->getReceiver();
		if ( ! ($this->_receiver === null) ) $XML .= $this->_receiver->toXML();
		if ($this->_senderID !== null) $XML .= "<senderID>".$this->_senderID."</senderID>\n";
		if ( $recursive ) if ( $this->_sender === null ) $this->getSender();
		if ( ! ($this->_sender === null) ) $XML .= $this->_sender->toXML();
		if ($this->_msg !== null) $XML .= "<msg>".recode_string("utf8..xml",$this->_msg)."</msg>\n";
		if ($this->_sentOn !== null) $XML .= "<sentOn>".recode_string("utf8..xml",$this->_sentOn)."</sentOn>\n";
		if ($this->_isRead !== null) $XML .= "<isRead>".$this->_isRead."</isRead>\n";
		$XML .= "</Message>\n";
		return $XML;
	}
}

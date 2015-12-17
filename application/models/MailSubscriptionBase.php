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
class Default_Model_MailSubscriptionBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_subjectType;
	protected $_events;
	protected $_researcherID;
	protected $_researcher;
	protected $_delivery;
	protected $_flt;
	protected $_unsubscribePassword;
	protected $_flthash;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid MailSubscription property: '$name'");
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
			throw new Exception("Invalid MailSubscription property: '$name'");
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

	public function setEvents($value)
	{
		/* if ( $value === null ) {
			$this->_events = 'NULL';
		} else */ $this->_events = $value;
		return $this;
	}

	public function getEvents()
	{
		return $this->_events;
	}

	public function setResearcherID($value)
	{
		/* if ( $value === null ) {
			$this->_researcherID = 'NULL';
		} else */ $this->_researcherID = $value;
		return $this;
	}

	public function getResearcherID()
	{
		return $this->_researcherID;
	}

	public function getResearcher()
	{
		if ( $this->_researcher === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getResearcherID());
			if ($Researchers->count() > 0) $this->_researcher = $Researchers->items[0];
		}
		return $this->_researcher;
	}

	public function setResearcher($value)
	{
		if ( $value === null ) {
			$this->setResearcherID(null);
		} else {
			$this->setResearcherID($value->getId());
		}
	}


	public function setDelivery($value)
	{
		/* if ( $value === null ) {
			$this->_delivery = 'NULL';
		} else */ $this->_delivery = $value;
		return $this;
	}

	public function getDelivery()
	{
		return $this->_delivery;
	}

	public function setFlt($value)
	{
		/* if ( $value === null ) {
			$this->_flt = 'NULL';
		} else */ $this->_flt = $value;
		return $this;
	}

	public function getFlt()
	{
		return $this->_flt;
	}

	public function setUnsubscribePassword($value)
	{
		/* if ( $value === null ) {
			$this->_unsubscribePassword = 'NULL';
		} else */ $this->_unsubscribePassword = $value;
		return $this;
	}

	public function getUnsubscribePassword()
	{
		return $this->_unsubscribePassword;
	}

	public function setFlthash($value)
	{
		/* if ( $value === null ) {
			$this->_flthash = 'NULL';
		} else */ $this->_flthash = $value;
		return $this;
	}

	public function getFlthash()
	{
		return $this->_flthash;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_MailSubscriptionsMapper());
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
		$XML = "<MailSubscription>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".recode_string("utf8..xml",$this->_name)."</name>\n";
		if ($this->_subjectType !== null) $XML .= "<subjectType>".recode_string("utf8..xml",$this->_subjectType)."</subjectType>\n";
		if ($this->_events !== null) $XML .= "<events>".$this->_events."</events>\n";
		if ($this->_researcherID !== null) $XML .= "<researcherID>".$this->_researcherID."</researcherID>\n";
		if ( $recursive ) if ( $this->_researcher === null ) $this->getResearcher();
		if ( ! ($this->_researcher === null) ) $XML .= $this->_researcher->toXML();
		if ($this->_delivery !== null) $XML .= "<delivery>".$this->_delivery."</delivery>\n";
		if ($this->_flt !== null) $XML .= "<flt>".recode_string("utf8..xml",$this->_flt)."</flt>\n";
		if ($this->_unsubscribePassword !== null) $XML .= "<unsubscribePassword>".recode_string("utf8..xml",$this->_unsubscribePassword)."</unsubscribePassword>\n";
		if ($this->_flthash !== null) $XML .= "<flthash>".$this->_flthash."</flthash>\n";
		$XML .= "</MailSubscription>\n";
		return $XML;
	}
}

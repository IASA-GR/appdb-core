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

class VMINetworkTrafficEntryBase
{
	protected $_mapper;
	protected $_id;
	protected $_ipRange;
	protected $_ports;
	protected $_netProtocolBits;
	protected $_flowBits;
	protected $_vmiInstanceID;
	protected $_vmiInstance;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid VMINetworkTrafficEntry property: '$name'");
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
			throw new Exception("Invalid VMINetworkTrafficEntry property: '$name'");
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

	public function setNetProtocolBits($value)
	{
		/* if ( $value === null ) {
			$this->_netProtocolBits = 'NULL';
		} else */ $this->_netProtocolBits = $value;
		return $this;
	}

	public function getNetProtocolBits()
	{
		return $this->_netProtocolBits;
	}

	public function setFlowBits($value)
	{
		/* if ( $value === null ) {
			$this->_flowBits = 'NULL';
		} else */ $this->_flowBits = $value;
		return $this;
	}

	public function getFlowBits()
	{
		return $this->_flowBits;
	}

	public function setIPrange($value)
	{
		/* if ( $value === null ) {
			$this->_flowBits = 'NULL';
		} else */ $this->_ipRange= $value;
		return $this;
	}

	public function getIPrange()
	{
		return $this->_ipRange;
	}

	public function setPorts($value)
	{
		/* if ( $value === null ) {
			$this->_ports = 'NULL';
		} else */ $this->_ports = $value;
		return $this;
	}

	public function getPorts()
	{
		return $this->_ports;
	}

	public function setVMIinstanceID($value)
	{
		/* if ( $value === null ) {
			$this->_vmiInstanceID = 'NULL';
		} else */ $this->_vmiInstanceID = $value;
		return $this;
	}

	public function getVMIinstanceID()
	{
		return $this->_vmiInstanceID;
	}

	public function getVMIinstance()
	{
		if ( $this->_vmiInstance === null ) {
			$VMIinstances = new VMIinstances();
			$VMIinstances->filter->id->equals($this->getVMIinstanceID());
			if ($VMIinstances->count() > 0) $this->_vmiInstance = $VMIinstances->items[0];
		}
		return $this->_vmiInstance;
	}

	public function setVMIinstance($value)
	{
		if ( $value === null ) {
			$this->setVMIinstanceID(null);
		} else {
			$this->setVMIinstanceID($value->getId());
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
			$this->setMapper(new VMINetworkTrafficMapper());
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
		$XML = "<VMINetworkTrafficEntry>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_flowBits !== null) $XML .= "<flowBits>".$this->_flowBits."</flowBits>\n";
		if ($this->_netProtocolBits !== null) $XML .= "<netProtocolBits>".$this->_netProtocolBits."</netProtocolBits>\n";
		if ($this->_ports !== null) $XML .= "<ports>".$this->_ports."</ports>\n";
		if ($this->_ipRange !== null) $XML .= "<ipRange>".$this->_ipRange."</ipRange>\n";
		if ( $recursive ) if ( $this->_vmiInstance === null ) $this->getVMIinstance();
		if ( ! ($this->_vmiInstance === null) ) $XML .= $this->_vmiInstance->toXML();
		$XML .= "</VMINetworkTrafficEntry>\n";
		return $XML;
	}
}

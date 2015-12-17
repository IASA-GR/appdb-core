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
class Default_Model_AppReleaseBase
{
	protected $_mapper;
	protected $_id;
	protected $_appID;
	protected $_application;
	protected $_release;
	protected $_series;
	protected $_state;
	protected $_addedon;
	protected $_publishedon;
	protected $_managerID;
	protected $_manager;
	protected $_lastupdated;
	protected $_releaseID;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid AppRelease property: '$name'");
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
			throw new Exception("Invalid AppRelease property: '$name'");
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

	public function setAppID($value)
	{
		/* if ( $value === null ) {
			$this->_appID = 'NULL';
		} else */ $this->_appID = $value;
		return $this;
	}

	public function getAppID()
	{
		return $this->_appID;
	}

	public function getApplication()
	{
		if ( $this->_application === null ) {
			$Applications = new Default_Model_Applications();
			$Applications->filter->id->equals($this->getAppID());
			if ($Applications->count() > 0) $this->_application = $Applications->items[0];
		}
		return $this->_application;
	}

	public function setApplication($value)
	{
		if ( $value === null ) {
			$this->setAppID(null);
		} else {
			$this->setAppID($value->getId());
		}
	}


	public function setRelease($value)
	{
		/* if ( $value === null ) {
			$this->_release = 'NULL';
		} else */ $this->_release = $value;
		return $this;
	}

	public function getRelease()
	{
		return $this->_release;
	}

	public function setSeries($value)
	{
		/* if ( $value === null ) {
			$this->_series = 'NULL';
		} else */ $this->_series = $value;
		return $this;
	}

	public function getSeries()
	{
		return $this->_series;
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

	public function setAddedon($value)
	{
		/* if ( $value === null ) {
			$this->_addedon = 'NULL';
		} else */ $this->_addedon = $value;
		return $this;
	}

	public function getAddedon()
	{
		return $this->_addedon;
	}

	public function setPublishedon($value)
	{
		/* if ( $value === null ) {
			$this->_publishedon = 'NULL';
		} else */ $this->_publishedon = $value;
		return $this;
	}

	public function getPublishedon()
	{
		return $this->_publishedon;
	}

	public function setManagerID($value)
	{
		/* if ( $value === null ) {
			$this->_managerID = 'NULL';
		} else */ $this->_managerID = $value;
		return $this;
	}

	public function getManagerID()
	{
		return $this->_managerID;
	}

	public function getManager()
	{
		if ( $this->_manager === null ) {
			$Researchers = new Default_Model_Researchers();
			$Researchers->filter->id->equals($this->getManagerID());
			if ($Researchers->count() > 0) $this->_manager = $Researchers->items[0];
		}
		return $this->_manager;
	}

	public function setManager($value)
	{
		if ( $value === null ) {
			$this->setManagerID(null);
		} else {
			$this->setManagerID($value->getId());
		}
	}


	public function setLastupdated($value)
	{
		/* if ( $value === null ) {
			$this->_lastupdated = 'NULL';
		} else */ $this->_lastupdated = $value;
		return $this;
	}

	public function getLastupdated()
	{
		return $this->_lastupdated;
	}

	public function setReleaseID($value)
	{
		/* if ( $value === null ) {
			$this->_releaseID = 'NULL';
		} else */ $this->_releaseID = $value;
		return $this;
	}

	public function getReleaseID()
	{
		return $this->_releaseID;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Default_Model_AppReleasesMapper());
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
		$XML = "<AppRelease>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_appID !== null) $XML .= "<appID>".$this->_appID."</appID>\n";
		if ( $recursive ) if ( $this->_application === null ) $this->getApplication();
		if ( ! ($this->_application === null) ) $XML .= $this->_application->toXML();
		if ($this->_release !== null) $XML .= "<release>".recode_string("utf8..xml",$this->_release)."</release>\n";
		if ($this->_series !== null) $XML .= "<series>".recode_string("utf8..xml",$this->_series)."</series>\n";
		if ($this->_state !== null) $XML .= "<state>".$this->_state."</state>\n";
		if ($this->_addedon !== null) $XML .= "<addedon>".recode_string("utf8..xml",$this->_addedon)."</addedon>\n";
		if ($this->_publishedon !== null) $XML .= "<publishedon>".recode_string("utf8..xml",$this->_publishedon)."</publishedon>\n";
		if ($this->_managerID !== null) $XML .= "<managerID>".$this->_managerID."</managerID>\n";
		if ( $recursive ) if ( $this->_manager === null ) $this->getManager();
		if ( ! ($this->_manager === null) ) $XML .= $this->_manager->toXML();
		if ($this->_lastupdated !== null) $XML .= "<lastupdated>".recode_string("utf8..xml",$this->_lastupdated)."</lastupdated>\n";
		if ($this->_releaseID !== null) $XML .= "<releaseID>".$this->_releaseID."</releaseID>\n";
		$XML .= "</AppRelease>\n";
		return $XML;
	}
}

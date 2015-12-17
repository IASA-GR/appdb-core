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
class Default_Model_DatasetLocationSiteBase
{
	protected $_mapper;
	protected $_id;
	protected $_datasetLocationID;
	protected $_datasetLocation;
	protected $_siteID;
	protected $_site;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid DatasetLocationSite property: '$name'");
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
			throw new Exception("Invalid DatasetLocationSite property: '$name'");
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

	public function setDatasetLocationID($value)
	{
		/* if ( $value === null ) {
			$this->_datasetLocationID = 'NULL';
		} else */ $this->_datasetLocationID = $value;
		return $this;
	}

	public function getDatasetLocationID()
	{
		return $this->_datasetLocationID;
	}

	public function getDatasetLocation()
	{
		if ( $this->_datasetLocation === null ) {
			$DatasetLocations = new Default_Model_DatasetLocations();
			$DatasetLocations->filter->id->equals($this->getDatasetLocationID());
			if ($DatasetLocations->count() > 0) $this->_datasetLocation = $DatasetLocations->items[0];
		}
		return $this->_datasetLocation;
	}

	public function setDatasetLocation($value)
	{
		if ( $value === null ) {
			$this->setDatasetLocationID(null);
		} else {
			$this->setDatasetLocationID($value->getId());
		}
	}


	public function setSiteID($value)
	{
		/* if ( $value === null ) {
			$this->_siteID = 'NULL';
		} else */ $this->_siteID = $value;
		return $this;
	}

	public function getSiteID()
	{
		return $this->_siteID;
	}

	public function getSite()
	{
		if ( $this->_site === null ) {
			$Sites = new Default_Model_Sites();
			$Sites->filter->id->equals($this->getSiteID());
			if ($Sites->count() > 0) $this->_site = $Sites->items[0];
		}
		return $this->_site;
	}

	public function setSite($value)
	{
		if ( $value === null ) {
			$this->setSiteID(null);
		} else {
			$this->setSiteID($value->getId());
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
			$this->setMapper(new Default_Model_DatasetLocationSitesMapper());
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
		$XML = "<DatasetLocationSite>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_datasetLocationID !== null) $XML .= "<datasetLocationID>".$this->_datasetLocationID."</datasetLocationID>\n";
		if ( $recursive ) if ( $this->_datasetLocation === null ) $this->getDatasetLocation();
		if ( ! ($this->_datasetLocation === null) ) $XML .= $this->_datasetLocation->toXML();
		if ($this->_siteID !== null) $XML .= "<siteID>".recode_string("utf8..xml",$this->_siteID)."</siteID>\n";
		if ( $recursive ) if ( $this->_site === null ) $this->getSite();
		if ( ! ($this->_site === null) ) $XML .= $this->_site->toXML();
		$XML .= "</DatasetLocationSite>\n";
		return $XML;
	}
}

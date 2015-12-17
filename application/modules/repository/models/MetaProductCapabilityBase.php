<?php
class Repository_Model_MetaProductCapabilityBase
{
	protected $_mapper;
	protected $_id;
	protected $_releaseId;
	protected $_metaProductRelease;
	protected $_capability;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid MetaProductCapability property: '$name'");
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
			throw new Exception("Invalid MetaProductCapability property: '$name'");
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

	public function setReleaseId($value)
	{
		/* if ( $value === null ) {
			$this->_releaseId = 'NULL';
		} else */ $this->_releaseId = $value;
		return $this;
	}

	public function getReleaseId()
	{
		return $this->_releaseId;
	}

	public function getMetaProductRelease()
	{
		if ( $this->_metaProductRelease === null ) {
			$MetaProductReleases = new Repository_Model_MetaProductReleases();
			$MetaProductReleases->filter->id->equals($this->getReleaseId());
			if ($MetaProductReleases->count() > 0) $this->_metaProductRelease = $MetaProductReleases->items[0];
		}
		return $this->_metaProductRelease;
	}

	public function setMetaProductRelease($value)
	{
		if ( $value === null ) {
			$this->setReleaseId(null);
		} else {
			$this->setReleaseId($value->getId());
		}
	}


	public function setCapability($value)
	{
		/* if ( $value === null ) {
			$this->_capability = 'NULL';
		} else */ $this->_capability = $value;
		return $this;
	}

	public function getCapability()
	{
		return $this->_capability;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Repository_Model_MetaProductCapabilitiesMapper());
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
		$XML = "<MetaProductCapability>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_releaseId !== null) $XML .= "<releaseId>".$this->_releaseId."</releaseId>\n";
		if ( $recursive ) if ( $this->_metaProductRelease === null ) $this->getMetaProductRelease();
		if ( ! ($this->_metaProductRelease === null) ) $XML .= $this->_metaProductRelease->toXML();
		if ($this->_capability !== null) $XML .= "<capability>".recode_string("utf8..xml",$this->_capability)."</capability>\n";
		$XML .= "</MetaProductCapability>\n";
		return $XML;
	}
}

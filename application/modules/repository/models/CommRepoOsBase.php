<?php
class Repository_Model_CommRepoOsBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_displayName;
	protected $_flavor;
	protected $_displayFlavor;
	protected $_artifactType;
	protected $_acronym;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid CommRepoOs property: '$name'");
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
			throw new Exception("Invalid CommRepoOs property: '$name'");
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

	public function setDisplayName($value)
	{
		/* if ( $value === null ) {
			$this->_displayName = 'NULL';
		} else */ $this->_displayName = $value;
		return $this;
	}

	public function getDisplayName()
	{
		return $this->_displayName;
	}

	public function setFlavor($value)
	{
		/* if ( $value === null ) {
			$this->_flavor = 'NULL';
		} else */ $this->_flavor = $value;
		return $this;
	}

	public function getFlavor()
	{
		return $this->_flavor;
	}

	public function setDisplayFlavor($value)
	{
		/* if ( $value === null ) {
			$this->_displayFlavor = 'NULL';
		} else */ $this->_displayFlavor = $value;
		return $this;
	}

	public function getDisplayFlavor()
	{
		return $this->_displayFlavor;
	}

	public function setArtifactType($value)
	{
		/* if ( $value === null ) {
			$this->_artifactType = 'NULL';
		} else */ $this->_artifactType = $value;
		return $this;
	}

	public function getArtifactType()
	{
		return $this->_artifactType;
	}

	public function setAcronym($value)
	{
		/* if ( $value === null ) {
			$this->_acronym = 'NULL';
		} else */ $this->_acronym = $value;
		return $this;
	}

	public function getAcronym()
	{
		return $this->_acronym;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Repository_Model_CommRepoOssMapper());
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
		$XML = "<CommRepoOs>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".$this->_name."</name>\n";
		if ($this->_displayName !== null) $XML .= "<displayName>".$this->_displayName."</displayName>\n";
		if ($this->_flavor !== null) $XML .= "<flavor>".$this->_flavor."</flavor>\n";
		if ($this->_displayFlavor !== null) $XML .= "<displayFlavor>".recode_string("utf8..xml",$this->_displayFlavor)."</displayFlavor>\n";
		if ($this->_artifactType !== null) $XML .= "<artifactType>".recode_string("utf8..xml",$this->_artifactType)."</artifactType>\n";
		if ($this->_acronym !== null) $XML .= "<acronym>".recode_string("utf8..xml",$this->_acronym)."</acronym>\n";
		$XML .= "</CommRepoOs>\n";
		return $XML;
	}
}

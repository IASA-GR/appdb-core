<?php
class Repository_Model_CommRepoRepositoryBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_basePath;
	protected $_baseUrl;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid CommRepoRepository property: '$name'");
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
			throw new Exception("Invalid CommRepoRepository property: '$name'");
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

	public function setBasePath($value)
	{
		/* if ( $value === null ) {
			$this->_basePath = 'NULL';
		} else */ $this->_basePath = $value;
		return $this;
	}

	public function getBasePath()
	{
		return $this->_basePath;
	}

	public function setBaseUrl($value)
	{
		/* if ( $value === null ) {
			$this->_baseUrl = 'NULL';
		} else */ $this->_baseUrl = $value;
		return $this;
	}

	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Repository_Model_CommRepoRepositoriesMapper());
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
		$XML = "<CommRepoRepository>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".$this->_name."</name>\n";
		if ($this->_basePath !== null) $XML .= "<basePath>".$this->_basePath."</basePath>\n";
		if ($this->_baseUrl !== null) $XML .= "<baseUrl>".$this->_baseUrl."</baseUrl>\n";
		$XML .= "</CommRepoRepository>\n";
		return $XML;
	}
}

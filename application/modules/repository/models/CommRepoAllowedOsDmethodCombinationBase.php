<?php
class Repository_Model_CommRepoAllowedOsDmethodCombinationBase
{
	protected $_mapper;
	protected $_id;
	protected $_osId;
	protected $_os;
	protected $_dMethodId;
	protected $_deployMethod;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid CommRepoAllowedOsDmethodCombination property: '$name'");
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
			throw new Exception("Invalid CommRepoAllowedOsDmethodCombination property: '$name'");
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

	public function setOsId($value)
	{
		/* if ( $value === null ) {
			$this->_osId = 'NULL';
		} else */ $this->_osId = $value;
		return $this;
	}

	public function getOsId()
	{
		return $this->_osId;
	}

	public function getOs()
	{
		if ( $this->_os === null ) {
			$CommRepoOss = new Repository_Model_CommRepoOss();
			$CommRepoOss->filter->id->equals($this->getOsId());
			if ($CommRepoOss->count() > 0) $this->_os = $CommRepoOss->items[0];
		}
		return $this->_os;
	}

	public function setOs($value)
	{
		if ( $value === null ) {
			$this->setOsId(null);
		} else {
			$this->setOsId($value->getId());
		}
	}


	public function setDMethodId($value)
	{
		/* if ( $value === null ) {
			$this->_dMethodId = 'NULL';
		} else */ $this->_dMethodId = $value;
		return $this;
	}

	public function getDMethodId()
	{
		return $this->_dMethodId;
	}

	public function getDeployMethod()
	{
		if ( $this->_deployMethod === null ) {
			$CommRepoDmethods = new Repository_Model_CommRepoDmethods();
			$CommRepoDmethods->filter->id->equals($this->getDMethodId());
			if ($CommRepoDmethods->count() > 0) $this->_deployMethod = $CommRepoDmethods->items[0];
		}
		return $this->_deployMethod;
	}

	public function setDeployMethod($value)
	{
		if ( $value === null ) {
			$this->setDMethodId(null);
		} else {
			$this->setDMethodId($value->getId());
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
			$this->setMapper(new Repository_Model_CommRepoAllowedOsDmethodCombinationsMapper());
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
		$XML = "<CommRepoAllowedOsDmethodCombination>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_osId !== null) $XML .= "<osId>".$this->_osId."</osId>\n";
		if ( $recursive ) if ( $this->_os === null ) $this->getOs();
		if ( ! ($this->_os === null) ) $XML .= $this->_os->toXML();
		if ($this->_dMethodId !== null) $XML .= "<dMethodId>".$this->_dMethodId."</dMethodId>\n";
		if ( $recursive ) if ( $this->_deployMethod === null ) $this->getDeployMethod();
		if ( ! ($this->_deployMethod === null) ) $XML .= $this->_deployMethod->toXML();
		$XML .= "</CommRepoAllowedOsDmethodCombination>\n";
		return $XML;
	}
}

<?php
class Repository_Model_CommRepoAllowedPlatformCombinationBase
{
	protected $_mapper;
	protected $_id;
	protected $_osId;
	protected $_os;
	protected $_archId;
	protected $_arch;
	protected $_canSupport;
	protected $_fsPattern;
	protected $_incRelSupport;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid CommRepoAllowedPlatformCombination property: '$name'");
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
			throw new Exception("Invalid CommRepoAllowedPlatformCombination property: '$name'");
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


	public function setArchId($value)
	{
		/* if ( $value === null ) {
			$this->_archId = 'NULL';
		} else */ $this->_archId = $value;
		return $this;
	}

	public function getArchId()
	{
		return $this->_archId;
	}

	public function getArch()
	{
		if ( $this->_arch === null ) {
			$CommRepoArchs = new Repository_Model_CommRepoArchs();
			$CommRepoArchs->filter->id->equals($this->getArchId());
			if ($CommRepoArchs->count() > 0) $this->_arch = $CommRepoArchs->items[0];
		}
		return $this->_arch;
	}

	public function setArch($value)
	{
		if ( $value === null ) {
			$this->setArchId(null);
		} else {
			$this->setArchId($value->getId());
		}
	}


	public function setCanSupport($value)
	{
		/* if ( $value === null ) {
			$this->_canSupport = 'NULL';
		} else */ $this->_canSupport = $value;
		return $this;
	}

	public function getCanSupport()
	{
		return $this->_canSupport;
	}

	public function setFsPattern($value)
	{
		/* if ( $value === null ) {
			$this->_fsPattern = 'NULL';
		} else */ $this->_fsPattern = $value;
		return $this;
	}

	public function getFsPattern()
	{
		return $this->_fsPattern;
	}

	public function setIncRelSupport($value)
	{
		/* if ( $value === null ) {
			$this->_incRelSupport = 'NULL';
		} else */ $this->_incRelSupport = $value;
		return $this;
	}

	public function getIncRelSupport()
	{
		return $this->_incRelSupport;
	}

	public function setMapper($mapper)
	{
		$this->_mapper = $mapper;
		return $this;
	}

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new Repository_Model_CommRepoAllowedPlatformCombinationsMapper());
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
		$XML = "<CommRepoAllowedPlatformCombination>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_osId !== null) $XML .= "<osId>".$this->_osId."</osId>\n";
		if ( $recursive ) if ( $this->_os === null ) $this->getOs();
		if ( ! ($this->_os === null) ) $XML .= $this->_os->toXML();
		if ($this->_archId !== null) $XML .= "<archId>".$this->_archId."</archId>\n";
		if ( $recursive ) if ( $this->_arch === null ) $this->getArch();
		if ( ! ($this->_arch === null) ) $XML .= $this->_arch->toXML();
		if ($this->_canSupport !== null) $XML .= "<canSupport>".recode_string("utf8..xml",$this->_canSupport)."</canSupport>\n";
		if ($this->_fsPattern !== null) $XML .= "<fsPattern>".recode_string("utf8..xml",$this->_fsPattern)."</fsPattern>\n";
		if ($this->_incRelSupport !== null) $XML .= "<incRelSupport>".recode_string("utf8..xml",$this->_incRelSupport)."</incRelSupport>\n";
		$XML .= "</CommRepoAllowedPlatformCombination>\n";
		return $XML;
	}
}

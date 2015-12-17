<?php
class Repository_Model_CommRepoStateBase
{
	protected $_mapper;
	protected $_id;
	protected $_name;
	protected $_repositoryId;
	protected $_commRepoRepository;

	public function __set($name,$value)
	{
		$method = 'set'.$name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception("Invalid CommRepoState property: '$name'");
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
			throw new Exception("Invalid CommRepoState property: '$name'");
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

	public function setRepositoryId($value)
	{
		/* if ( $value === null ) {
			$this->_repositoryId = 'NULL';
		} else */ $this->_repositoryId = $value;
		return $this;
	}

	public function getRepositoryId()
	{
		return $this->_repositoryId;
	}

	public function getCommRepoRepository()
	{
		if ( $this->_commRepoRepository === null ) {
			$CommRepoRepositories = new Repository_Model_CommRepoRepositories();
			$CommRepoRepositories->filter->id->equals($this->getRepositoryId());
			if ($CommRepoRepositories->count() > 0) $this->_commRepoRepository = $CommRepoRepositories->items[0];
		}
		return $this->_commRepoRepository;
	}

	public function setCommRepoRepository($value)
	{
		if ( $value === null ) {
			$this->setRepositoryId(null);
		} else {
			$this->setRepositoryId($value->getId());
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
			$this->setMapper(new Repository_Model_CommRepoStatesMapper());
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
		$XML = "<CommRepoState>\n";
		if ($this->_id !== null) $XML .= "<id>".$this->_id."</id>\n";
		if ($this->_name !== null) $XML .= "<name>".$this->_name."</name>\n";
		if ($this->_repositoryId !== null) $XML .= "<repositoryId>".$this->_repositoryId."</repositoryId>\n";
		if ( $recursive ) if ( $this->_commRepoRepository === null ) $this->getCommRepoRepository();
		if ( ! ($this->_commRepoRepository === null) ) $XML .= $this->_commRepoRepository->toXML();
		$XML .= "</CommRepoState>\n";
		return $XML;
	}
}
